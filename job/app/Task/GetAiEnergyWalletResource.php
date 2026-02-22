<?php
namespace App\Task;

use App\Model\Energy\EnergyAiTrusteeship;
use App\Model\Energy\EnergyAiBishu;
use App\Model\Telegram\TelegramBotUser;
use App\Library\Log;
use Hyperf\Utils\Coroutine\Concurrent;

class GetAiEnergyWalletResource
{
    public function execute()
    { 
        //智能托管
        try {
            $data = EnergyAiTrusteeship::from('t_energy_ai_trusteeship as a')
                    ->Join('t_telegram_bot as b','a.bot_rid','b.rid')
                    ->Join('energy_platform_bot as c','a.bot_rid','c.bot_rid')
                    ->Join('telegram_bot_user as d', function ($join) {
                                      $join->on('a.bot_rid', '=','d.bot_rid')->on('a.tg_uid', '=','d.tg_uid');
                                      })
                    ->where('a.status',0)
                    ->where('c.is_open_ai_trusteeship','Y')
                    ->whereRaw('length(a.wallet_addr) = 34')
                    ->whereRaw('d.cash_trx > c.trx_price_energy_32000 and (a.max_buy_quantity = 0 or (a.max_buy_quantity > 0 and a.total_buy_quantity < a.max_buy_quantity))')
                    ->select('a.*','b.bot_token')
                    ->get();
                    
            if($data->count() > 0){
                //协程数量
                $concurrent = new Concurrent(5);
                
                foreach ($data as $k => $v) {
                    $concurrent->create(function () use ($v) {
                        sleep(1); //不容易被api限制
                        $isSuccess = 'N'; //是否成功,调用trongrid
                        $url = 'https://apilist.tronscanapi.com/api/accountv2?address='.$v['wallet_addr'];
                        
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
                                    $updatedata = [];
                                    $bandwidth = $res['bandwidth']['freeNetRemaining'] + $res['bandwidth']['netRemaining'];
                                    $energy = $res['bandwidth']['energyRemaining'];
                                    
                                    //低于最低值的时候,则需要下单,这里改为-100,代理的能量有波动,减100也可以转成功
                                    $rongcuo = $v['min_energy_quantity'] >= 131000 ?700:100;
                                    if($energy < ($v['min_energy_quantity'] - 100) && $v['is_buy'] == 'N'){
                                        $updatedata['is_buy'] = 'Y';
                                    }
                                    $updatedata['current_bandwidth_quantity'] = $bandwidth;
                                    $updatedata['current_energy_quantity'] = $energy;
                                    EnergyAiTrusteeship::where('rid',$v['rid'])->update($updatedata);
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
                                "address" => $v['wallet_addr'],
                                "visible" => true
                            ];
                            
                            $res2 = curl_post_https($balance_url,json_encode($param),$heders);
                            
                            if(empty($res2)){
                                //为空则什么都不处理
                            }else{
                                $res2 = json_decode($res2,true);
                                if(isset($res2['freeNetLimit'])){
                                    $updatedata = [];
                                    $bandwidth = ($res2['NetLimit'] ?? 0) + ($res2['freeNetLimit'] ?? 0) - ($res2['NetUsed'] ?? 0) ;
                                    $energy = ($res2['EnergyLimit'] ?? 0) - ($res2['EnergyUsed'] ?? 0);
                                    
                                    //低于最低值的时候,则需要下单,这里改为-100,代理的能量有波动,减100也可以转成功
                                    $rongcuo = $v['min_energy_quantity'] >= 131000 ?700:100;
                                    if($energy < ($v['min_energy_quantity'] - $rongcuo) && $v['is_buy'] == 'N'){
                                        $updatedata['is_buy'] = 'Y';
                                    }
                                    $updatedata['current_bandwidth_quantity'] = $bandwidth;
                                    $updatedata['current_energy_quantity'] = $energy;
                                    EnergyAiTrusteeship::where('rid',$v['rid'])->update($updatedata);
                                }
                            }
                        }
                    });
                }
            }
            
        }catch (\Exception $e){
            $this->log('energyplatformbalance','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //笔数套餐
        try {
            $data = EnergyAiBishu::from('t_energy_ai_bishu as a')
                    ->Join('t_telegram_bot as b','a.bot_rid','b.rid')
                    ->Join('energy_platform_bot as c','a.bot_rid','c.bot_rid')
                    ->where('a.status',0)
                    ->where('c.is_open_bishu','Y')
                    ->where('a.is_buy','N')
                    ->whereRaw('length(a.wallet_addr) = 34 and a.max_buy_quantity > a.total_buy_quantity')
                    // ->whereRaw("a.last_buy_time <= DATE_SUB(NOW(), INTERVAL 1 MINUTE)") //限制1分钟给一次，如果用了区块监控,建议开启这个
                    ->select('a.*','b.bot_token')
                    ->get();
                    
            if($data->count() > 0){
                //协程数量
                $concurrent = new Concurrent(5);
                
                foreach ($data as $k => $v) {
                    $concurrent->create(function () use ($v) {
                        sleep(1); //不容易被api限制
                        $isSuccess = 'N'; //是否成功,调用trongrid
                        $url = 'https://apilist.tronscanapi.com/api/accountv2?address='.$v['wallet_addr'];
                        
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
                                    $updatedata = [];
                                    $bandwidth = $res['bandwidth']['freeNetRemaining'] + $res['bandwidth']['netRemaining'];
                                    $energy = $res['bandwidth']['energyRemaining'];
                                    
                                    //低于最低值的时候,则需要下单,这里改为-100,代理的能量有波动,减100也可以转成功
                                    $rongcuo = $v['per_bishu_energy_quantity'] >= 131000 ?700:100;
                                    if($energy < ($v['per_bishu_energy_quantity'] - $rongcuo) && $v['is_buy'] == 'N'){
                                        $updatedata['is_buy'] = 'Y';
                                        
                                        $this->log('energyplatformbalance',$v['wallet_addr'].'，笔数检测（tronscan）需要下单，检测地址剩余能量：'.$energy);
                                    }
                                    $updatedata['current_bandwidth_quantity'] = $bandwidth;
                                    $updatedata['current_energy_quantity'] = $energy;
                                    EnergyAiBishu::where('rid',$v['rid'])->where('is_buy','N')->update($updatedata);
                                    // EnergyAiBishu::where('rid',$v['rid'])->where('is_buy','N')->whereRaw("last_buy_time <= DATE_SUB(NOW(), INTERVAL 1 MINUTE)")->update($updatedata);
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
                                "address" => $v['wallet_addr'],
                                "visible" => true
                            ];
                            
                            $res2 = curl_post_https($balance_url,json_encode($param),$heders);
                            
                            if(empty($res2)){
                                //为空则什么都不处理
                            }else{
                                $res2 = json_decode($res2,true);
                                if(isset($res2['freeNetLimit'])){
                                    $updatedata = [];
                                    $bandwidth = ($res2['NetLimit'] ?? 0) + ($res2['freeNetLimit'] ?? 0) - ($res2['NetUsed'] ?? 0) ;
                                    $energy = ($res2['EnergyLimit'] ?? 0) - ($res2['EnergyUsed'] ?? 0);
                                    
                                    //低于最低值的时候,则需要下单,这里改为-100,代理的能量有波动,减100也可以转成功
                                    $rongcuo = $v['per_bishu_energy_quantity'] >= 131000 ?700:100;
                                    if($energy < ($v['per_bishu_energy_quantity'] - $rongcuo) && $v['is_buy'] == 'N'){
                                        $updatedata['is_buy'] = 'Y';
                                        
                                        $this->log('energyplatformbalance',$v['wallet_addr'].'，笔数检测（trongrid）需要下单，检测地址剩余能量：'.$energy);
                                    }
                                    $updatedata['current_bandwidth_quantity'] = $bandwidth;
                                    $updatedata['current_energy_quantity'] = $energy;
                                    EnergyAiBishu::where('rid',$v['rid'])->where('is_buy','N')->update($updatedata);
                                    // EnergyAiBishu::where('rid',$v['rid'])->where('is_buy','N')->whereRaw("last_buy_time <= DATE_SUB(NOW(), INTERVAL 1 MINUTE)")->update($updatedata);
                                }
                            }
                        }
                    });
                }
            }
            
        }catch (\Exception $e){
            $this->log('energyplatformbalance','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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