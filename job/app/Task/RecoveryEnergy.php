<?php
namespace App\Task;

use App\Model\Energy\EnergyPlatformOrder;
use App\Service\RsaServices;
use App\Library\Log;

class RecoveryEnergy
{
    public function execute()
    { 
        try {
            //代理期限:0一小时,1一天,3三天
            $data = EnergyPlatformOrder::from('t_energy_platform_order as a')
                    ->Join('energy_platform as b','a.energy_platform_rid','b.rid')
                    ->where(function($query){
                        $query->where('a.recovery_status','=',2)->where('a.energy_day','=',0)->where('a.energy_time','<=',date('Y-m-d H:i:s',strtotime('-30 minutes', strtotime(nowDate()))));
                    })
                    ->orWhere(function($query){
                        $query->where('a.recovery_status','=',2)->where('a.energy_day','=',1)->where('a.energy_time','<=',date('Y-m-d H:i:s',strtotime('-1 day', strtotime(nowDate()))));
                    })
                    ->orWhere(function($query){
                        $query->where('a.recovery_status','=',2)->where('a.energy_day','=',3)->where('a.energy_time','<=',date('Y-m-d H:i:s',strtotime('-3 day', strtotime(nowDate()))));
                    })
                    ->orWhere(function($query){
                        $query->where('a.recovery_status','=',2)->where('a.energy_day','=',30)->where('a.energy_time','<=',date('Y-m-d H:i:s',strtotime('-30 day', strtotime(nowDate()))));
                    })
                    ->select('a.rid','b.platform_uid','b.platform_apikey','a.energy_amount','a.receive_address','a.platform_name','b.permission_id','a.use_trx')
                    ->limit(50)->get();

            if($data->count() > 0){
                $rsa_services = new RsaServices();
                
                foreach ($data as $k => $v) {
                    $time = nowDate();
                    
                    //自己质押代理
                    if($v->platform_name == 3){
                        $signstr = $rsa_services->privateDecrypt($v->platform_apikey);
                        
                        $params = [
                            'pri' => $signstr,
                            'fromaddress' => $v->platform_uid,
                            'receiveaddress' => $v->receive_address,
                            'resourcename' => 'ENERGY',
                            'resourceamount' => empty($v->use_trx) ?$v->energy_amount:$v->use_trx,
                            'resourcetype' => empty($v->use_trx) ?2:3, //资源方式：1代理资源,2回收资源(按能量),3回收资源(按TRX)
                            'permissionid' => $v->permission_id
                        ];
                        
                        $apiWebUrl = config('services.api_web.url');
                        $res = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete',$params);
                        
                        if(empty($res)){
                            $this->log('recoveryenergy',$v->rid.'：能量回收失败1，为空');
                            continue;
                        }else{
                            $res = json_decode($res,true);
                            
                            if($res['code'] && $res['code'] == 200){
                                $save_data = [];
                                $save_data['recovery_status'] = 3; //已回收
                                $save_data['recovery_time'] = $time;
                                
                                EnergyPlatformOrder::where('rid',$v->rid)->update($save_data);
                                continue;
                            }else{
                                $this->log('recoveryenergy',$v->rid.'：能量回收失败2：'.json_encode($res));
                                
                                //失败重试一次,70秒内只能重试一次
                                $isFail = getRedis('failrecovery'.$v->rid);
                                if(empty($isFail)){
                                    $this->log('recoveryenergy',$v->rid.'，重试回收一次');
                                    setexRedis('failrecovery'.$v->rid,70,'rerun:'.$time);
                                }else{
                                    //能量回收失败的时候,查该地址具体还有多少代理能量
                                    $checkParams = [
                                        'fromaddress' => $v->platform_uid,
                                        'toaddress' => $v->receive_address
                                    ];
                                    
                                    $apiWebUrl = config('services.api_web.url');
                                    $checkRes = Get_Pay($apiWebUrl . '/api/tron/getdelegatedaddress', $checkParams);
                                    if(empty($checkRes)){
                                        $this->log('recoveryenergy',$v->rid.'：能量回收检查失败1，为空');
                                        continue;
                                    }else{
                                        $checkRes = json_decode($checkRes,true);
                                        if($checkRes['code'] && $checkRes['code'] == 200){
                                            if($checkRes['data']['energydelegate'] == 0){
                                                $this->log('recoveryenergy',$v->rid.'，查到质押为0，直接改为已回收！！！');
                                                $save_data = [];
                                                $save_data['recovery_status'] = 3; //已回收
                                                $save_data['recovery_time'] = $time;
                                                
                                                EnergyPlatformOrder::where('rid',$v->rid)->update($save_data);
                                                continue;
                                            }else{
                                                $save_data = [];
                                                $save_data['use_trx'] = $checkRes['data']['energydelegate'];
                                                
                                                EnergyPlatformOrder::where('rid',$v->rid)->update($save_data);
                                                continue;
                                            }
                                        }else{
                                            $this->log('recoveryenergy',$v->rid.'：能量回收检查失败2：'.json_encode($checkRes));
                                            continue;
                                        }
                                    }
                                }
                                
                                continue;
                            }
                        }
                    }
                }

            }else{
                // $this->log('recoveryenergy','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('recoveryenergy','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
    }

    /**
     * 记入日志
     * @param $log_title [日志路径]
     * @param $message [内容，不支持数组]
     * @param $remarks [备注]
    */
    protected function log($log_title,$message,$remarks='info'){
        Log::get($remarks,$log_title)->info($message);
    }

}