<?php
namespace App\Task;

use App\Model\Energy\EnergyPlatform;
use App\Library\Log;
use App\Service\RsaServices;
use App\Model\Telegram\TelegramBotUser;

class GetEnergyPlatformBalance
{
    public function execute()
    { 
        try {
            $data = EnergyPlatform::from('t_energy_platform as a')
                    ->leftJoin('t_telegram_bot as b','a.tg_notice_bot_rid','b.rid')
                    ->where('a.status',0)
                    ->select('a.*','b.bot_token')
                    ->get();
            
            if($data->count() > 0){
                $rsa_services = new RsaServices();
                
                foreach ($data as $k => $v) {
                    //neee.cc平台
                    if($v['platform_name'] == 1){
                        $header = [
                            "Content-Type:application/json"
                        ];
                        
                        $param = [
                            "uid" => $v['platform_uid'],
                            "time" => time()
                        ];
                        
                		ksort($param);
                		reset($param);
                		
                		$signstr = $rsa_services->privateDecrypt($v['platform_apikey']);
                	
                		foreach($param as $k1 => $v1){
                			if($k1 != "sign" && $k1 != "sign_type" && $v1!=''){
                				$signstr .= $k1.$v1;
                			}
                		}
                		
                		$sign = md5($signstr);
                		$param['sign'] = $sign;
                        $balance_url = 'https://api.tronqq.com/openapi/v1/user/balance';
                        $res = Get_Pay($balance_url,json_encode($param),$header);
                        
                        if(empty($res)){
                            $this->log('energyplatformbalance',$v['rid'].'平台请求失败');
                        }else{
                            $res = json_decode($res,true);
                            if($res['status'] == 200){
                                if(empty($res['data'])){
                                    $this->log('energyplatformbalance',$v['rid'].'平台请求失败2:'.json_encode($res));
                                }else{
                                    $balance = $res['data']['balance'];
                                    $balance = $balance <= 0 ?0:$balance;
                                    
                                    $updatedata1['platform_balance'] = $balance;
                                    //间隔10分钟通知一次
                                    if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                        $updatedata1['last_alert_time'] = nowDate();
                                        
                                        //余额通知管理员
                                        if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                            $replytext = "能量平台(neee.cc)，余额不足，请立即前往平台充值！\n"
                                                        ."能量平台ID：".$v['rid']."\n"
                                                        ."平台用户UID：".$v['platform_uid']."\n"
                                                        ."当前余额：".$balance."\n"
                                                        ."告警金额：".$v['alert_platform_balance']."\n\n"
                                                        ."不处理会一直告警通知！间隔10分钟告警一次";
                                                        
                                            $sendlist = explode(',',$v['tg_notice_obj']);
                            
                                            foreach ($sendlist as $x => $y) {
                                                $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                                
                                                Get_Pay($sendmessageurl);
                                            }
                                        }
                                    }
                                    
                                    EnergyPlatform::where('rid',$v['rid'])->update($updatedata1);
                                }
                            }else{
                                $this->log('energyplatformbalance',$v['rid'].'平台请求失败3:'.json_encode($res));
                            }
                        }
                    }
                    
                    //RentEnergysBot平台
                    elseif($v['platform_name'] == 2){
                        $signstr = $rsa_services->privateDecrypt($v['platform_apikey']);
                        $balance_url = 'https://api.wallet.buzz?api=getBalance&apikey='.$signstr;
                        $res = Get_Pay($balance_url);
                        
                        if(!isset($res) || $res === ""){
                            $this->log('energyplatformbalance',$v['rid'].'平台请求失败1:');
                        }else{
                            $balance = $res <= 0 ?0:$res;
                                    
                            $updatedata2['platform_balance'] = $balance;
                            
                            if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                $updatedata2['last_alert_time'] = nowDate();
                                
                                //余额通知管理员
                                if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                    $replytext = "能量平台(RentEnergysBot)，余额不足，请立即前往平台充值！\n"
                                                ."能量平台ID：".$v['rid']."\n"
                                                ."平台用户UID：".$v['platform_uid']."\n"
                                                ."当前余额：".$balance."\n"
                                                ."告警金额：".$v['alert_platform_balance']."\n\n"
                                                ."不处理会一直告警通知！间隔10分钟告警一次";
                                                
                                    $sendlist = explode(',',$v['tg_notice_obj']);
                    
                                    foreach ($sendlist as $x => $y) {
                                        $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                        
                                        Get_Pay($sendmessageurl);
                                    }
                                }
                            }
                            
                            EnergyPlatform::where('rid',$v['rid'])->update($updatedata2);
                        }
                    }
                    
                    //自己质押代理
                    elseif($v['platform_name'] == 3 && mb_strlen($v['platform_uid']) == 34){
                        $tronurl = 'https://api.trongrid.io/wallet/getaccountresource';
                
                        $api_key = config('apikey.gridapikey');
                        $apikeyrand = $api_key[array_rand($api_key)];
                        
                        $heders = [
                            "TRON-PRO-API-KEY:".$apikeyrand
                        ];
                        
                        $body = [
                          "address" => $v['platform_uid'],
                          "visible" => true
                        ];
                        
                        $tronres = Get_Pay($tronurl,json_encode($body),$heders);

                        if(empty($tronres)){
                            $this->log('energyplatformbalance',$v['rid'].'平台请求失败1:');
                        }else{
                            $tronres = json_decode($tronres,true);

                            if(isset($tronres['EnergyLimit'])){
                                $balance = $tronres['EnergyLimit'] - ($tronres['EnergyUsed'] ?? 0);
                                $updatedata3['platform_balance'] = $balance;
                                
                                if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                    $updatedata3['last_alert_time'] = nowDate();
                                    
                                    //余额通知管理员
                                    if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                        $replytext = "能量平台(自己质押代理)，能量不足，请立即质押！\n"
                                                    ."能量平台ID：".$v['rid']."\n"
                                                    ."质押钱包地址：".$v['platform_uid']."\n"
                                                    ."当前能量剩余：".$balance."\n"
                                                    ."告警能量值：".$v['alert_platform_balance']."\n\n"
                                                    ."不处理会一直告警通知！间隔10分钟告警一次";
                                                    
                                        $sendlist = explode(',',$v['tg_notice_obj']);
                        
                                        foreach ($sendlist as $x => $y) {
                                            $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                            
                                            Get_Pay($sendmessageurl);
                                        }
                                    }
                                }
                                
                                EnergyPlatform::where('rid',$v['rid'])->update($updatedata3);
                            }
                        }
                    }
                    
                    //trongas.io平台
                    elseif($v['platform_name'] == 4){
                        $param = [
                            "username" => $v['platform_uid']
                        ];
                        $balance_url = 'https://trongas.io/api/userInfo';
                        $res = Get_Pay($balance_url,$param);
                        
                        if(empty($res)){
                            $this->log('energyplatformbalance',$v['rid'].'平台请求失败');
                        }else{
                            $res = json_decode($res,true);
                            if($res['code'] == 10000){
                                if(empty($res['data'])){
                                    $this->log('energyplatformbalance',$v['rid'].'平台请求失败2:'.json_encode($res));
                                }else{
                                    $balance = $res['data']['balance'];
                                    $balance = $balance <= 0 ?0:$balance;
                                    
                                    $updatedata4['platform_balance'] = $balance;
                                    //间隔10分钟通知一次
                                    if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                        $updatedata4['last_alert_time'] = nowDate();
                                        
                                        //余额通知管理员
                                        if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                            $replytext = "能量平台(trongas.io)，余额不足，请立即前往平台充值！\n"
                                                        ."能量平台ID：".$v['rid']."\n"
                                                        ."平台用户UID：".$v['platform_uid']."\n"
                                                        ."当前余额：".$balance."\n"
                                                        ."告警金额：".$v['alert_platform_balance']."\n\n"
                                                        ."不处理会一直告警通知！间隔10分钟告警一次";
                                                        
                                            $sendlist = explode(',',$v['tg_notice_obj']);
                            
                                            foreach ($sendlist as $x => $y) {
                                                $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                                
                                                Get_Pay($sendmessageurl);
                                            }
                                        }
                                    }
                                    
                                    EnergyPlatform::where('rid',$v['rid'])->update($updatedata4);
                                }
                            }else{
                                $this->log('energyplatformbalance',$v['rid'].'平台请求失败3:'.json_encode($res));
                            }
                        }
                    }

                    //搜狐平台
                    elseif($v['platform_name'] == 6){
                        $signstr = $rsa_services->privateDecrypt($v['platform_apikey']);
                        
                        $param = [
                            "token" => $signstr
                        ];
                        $balance_url = 'https://admin.weidubot.cc/api/trc_api/user_info';
                        $res = Get_Pay($balance_url,$param);
                        
                        if(empty($res)){
                            $this->log('energyplatformbalance',$v['rid'].'平台请求失败');
                        }else{
                            $res = json_decode($res,true);
                            if($res['code'] == 1){
                                if(empty($res['data'])){
                                    $this->log('energyplatformbalance',$v['rid'].'平台请求失败2:'.json_encode($res));
                                }else{
                                    $balance = $res['data']['balance'];
                                    $balance = $balance <= 0 ?0:$balance;
                                    
                                    $updatedata4['platform_balance'] = $balance;
                                    //间隔10分钟通知一次
                                    if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                        $updatedata4['last_alert_time'] = nowDate();
                                        
                                        //余额通知管理员
                                        if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                            $replytext = "能量平台(搜狐)，余额不足，请立即前往平台充值！\n"
                                                        ."能量平台ID：".$v['rid']."\n"
                                                        ."平台用户UID：".$v['platform_uid']."\n"
                                                        ."当前余额：".$balance."\n"
                                                        ."告警金额：".$v['alert_platform_balance']."\n\n"
                                                        ."不处理会一直告警通知！间隔10分钟告警一次";
                                                        
                                            $sendlist = explode(',',$v['tg_notice_obj']);
                            
                                            foreach ($sendlist as $x => $y) {
                                                $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                                
                                                Get_Pay($sendmessageurl);
                                            }
                                        }
                                    }
                                    
                                    EnergyPlatform::where('rid',$v['rid'])->update($updatedata4);
                                }
                            }else{
                                $this->log('energyplatformbalance',$v['rid'].'平台请求失败3:'.json_encode($res));
                            }
                        }
                    }
                    
                    //机器人开发者代理
                    elseif($v['platform_name'] == 5){
                        $balance_url = env('THIRD_URL');
                        if(!empty($balance_url)){
                            $balance_url = $balance_url.'/api/thirdpart/balance?tg_uid='.$v['platform_uid'];
                            $res = Get_Pay($balance_url);
                            
                            if(empty($res)){
                                $this->log('energyplatformbalance',$v['rid'].'平台请求失败');
                            }else{
                                $res = json_decode($res,true);
                                if($res['code'] == 200){
                                    if(empty($res['data'])){
                                        $this->log('energyplatformbalance',$v['rid'].'平台请求失败2:'.json_encode($res));
                                    }else{
                                        $balance = $res['data']['trx_balance'];
                                        $balance = $balance <= 0 ?0:$balance;
                                        
                                        $updatedata5['platform_balance'] = $balance;
                                        //间隔10分钟通知一次
                                        if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                            $updatedata5['last_alert_time'] = nowDate();
                                            
                                            //余额通知管理员
                                            if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                                $replytext = "能量平台(机器人开发者代理)，余额不足，请立即联系客服充值！\n"
                                                            ."能量平台ID：".$v['rid']."\n"
                                                            ."平台用户UID：".$v['platform_uid']."\n"
                                                            ."当前余额：".$balance."\n"
                                                            ."告警金额：".$v['alert_platform_balance']."\n\n"
                                                            ."不处理会一直告警通知！间隔10分钟告警一次";
                                                            
                                                $sendlist = explode(',',$v['tg_notice_obj']);
                                
                                                foreach ($sendlist as $x => $y) {
                                                    $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                                    
                                                    Get_Pay($sendmessageurl);
                                                }
                                            }
                                        }
                                        
                                        EnergyPlatform::where('rid',$v['rid'])->update($updatedata5);
                                    }
                                }else{
                                    $this->log('energyplatformbalance',$v['rid'].'平台请求失败3:'.json_encode($res));
                                }
                            }
                        }
                    }
                    
                    //NL-API平台（tgnl-home能量池系统）
                    elseif($v['platform_name'] == 7){
                        // 获取tgnl-home域名，优先从环境变量，其次从comments字段
                        $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
                        if(empty($nlApiBaseUrl) && !empty($v['comments'])){
                            // 尝试从comments中解析域名（格式：nl_api_url=https://xxx.com）
                            if(preg_match('/nl_api_url=([^\s]+)/i', $v['comments'], $matches)){
                                $nlApiBaseUrl = trim($matches[1]);
                            }
                        }
                        
                        if(empty($nlApiBaseUrl)){
                            $this->log('energyplatformbalance',$v['rid'].'NL-API域名未配置');
                            continue;
                        }
                        
                        // platform_uid 作为 API username
                        $apiUsername = $v['platform_uid'];
                        // platform_apikey 解密后作为 API password
                        $apiPassword = $rsa_services->privateDecrypt($v['platform_apikey']);
                        
                        if(empty($apiUsername) || empty($apiPassword)){
                            $this->log('energyplatformbalance',$v['rid'].'NL-API账户或密码未配置');
                            continue;
                        }
                        
                        // 调用 /v1/get_api_user_info 获取余额
                        $balance_url = rtrim($nlApiBaseUrl, '/') . '/v1/get_api_user_info?username=' . urlencode($apiUsername) . '&password=' . urlencode($apiPassword);
                        $res = Get_Pay($balance_url);
                        
                        if(empty($res)){
                            $this->log('energyplatformbalance',$v['rid'].'NL-API平台请求失败');
                        }else{
                            $res = json_decode($res,true);
                            // 检查是否有错误
                            if(isset($res['error'])){
                                $this->log('energyplatformbalance',$v['rid'].'NL-API平台请求失败:'.$res['error']);
                            }elseif(isset($res['当前余额(TRX)'])){
                                // 成功获取余额
                                $balance = floatval($res['当前余额(TRX)']);
                                $balance = $balance <= 0 ?0:$balance;
                                
                                $updatedata7['platform_balance'] = $balance;
                                
                                //间隔10分钟通知一次
                                if($balance <= $v['alert_platform_balance'] && $v['alert_platform_balance'] > 0 && strtotime($v['last_alert_time']) + 600 <= strtotime(nowDate())){
                                    $updatedata7['last_alert_time'] = nowDate();
                                    
                                    //余额通知管理员
                                    if($v['tg_notice_obj'] && !empty($v['tg_notice_obj'])){
                                        $replytext = "能量平台(NL-API)，余额不足，请立即前往能量池系统充值！\n"
                                                    ."能量平台ID：".$v['rid']."\n"
                                                    ."API用户名：".$apiUsername."\n"
                                                    ."当前余额：".$balance." TRX\n"
                                                    ."告警金额：".$v['alert_platform_balance']." TRX\n\n"
                                                    ."不处理会一直告警通知！间隔10分钟告警一次";
                                                    
                                        $sendlist = explode(',',$v['tg_notice_obj']);
                        
                                        foreach ($sendlist as $x => $y) {
                                            $sendmessageurl = 'https://api.telegram.org/bot'.$v['bot_token'].'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                            
                                            Get_Pay($sendmessageurl);
                                        }
                                    }
                                }
                                
                                EnergyPlatform::where('rid',$v['rid'])->update($updatedata7);
                            }else{
                                $this->log('energyplatformbalance',$v['rid'].'NL-API平台请求失败2:'.json_encode($res));
                            }
                        }
                    }
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