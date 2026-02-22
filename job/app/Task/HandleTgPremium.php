<?php
namespace App\Task;

use App\Model\Premium\PremiumWalletTradeList;
use App\Model\Premium\PremiumPlatformPackage;
use App\Model\Premium\PremiumPlatformOrder;
use App\Model\Premium\PremiumPlatform;
use App\Service\RsaServices;
use App\Library\Log;

class HandleTgPremium
{
    public function execute()
    { 
        try {
            $data = PremiumWalletTradeList::from('premium_wallet_trade_list as a')
                ->join('premium_platform as b','a.transferto_address','b.receive_wallet')
                ->where('a.process_status',1)
                ->where('a.coin_name','usdt')
                ->select('a.rid','a.transferfrom_address','a.amount','b.rid as premium_platform_rid','b.platform_cookie','b.platform_hash','b.status','b.platform_name','b.platform_phrase','a.platform_order_rid')
                ->limit(100)
                ->get();

            if($data->count() > 0){
                foreach ($data as $k => $v) {
                    $time = nowDate();
                    
                    if($v->platform_order_rid){
                        $res = PremiumPlatformOrder::where('rid',$v->platform_order_rid)->where('status',1)->first();
                    }else{
                        //匹配金额
                        $res = PremiumPlatformOrder::where('premium_platform_rid',$v->premium_platform_rid)->where('need_pay_usdt',$v->amount)->where('status',0)->first();
                    }
                    
                    if(empty($res)){
                        $save_data = [];
                        $save_data['process_status'] = 7;  //金额无对应订单
                        $save_data['process_comments'] = '金额无对应订单';      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        $save_data['premium_platform_rid'] = $v->premium_platform_rid;
                        PremiumWalletTradeList::where('rid',$v->rid)->update($save_data);
                        continue;
                    }else{
                        $save_data = [];
                        $save_data['status'] = 1;  //待开通
                        PremiumPlatformOrder::where('rid',$res['rid'])->update($save_data);
                        
                        $save_data = [];
                        $save_data['process_status'] = 9;      //下单成功
                        $save_data['process_comments'] = 'SUCCESS';      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        
                        PremiumWalletTradeList::where('rid',$v->rid)->update($save_data);
                    }
                }

            }else{
                // $this->log('tgpremium','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('tgpremium','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //支付成功后开通
        try {
            $data = PremiumPlatformOrder::where('status',1)->limit(100)->get();
            
            if($data->count() > 0){
                foreach ($data as $k => $v) {
                    $time = nowDate();
                    
                    $platform = PremiumPlatform::where('bot_rid',$v->bot_rid)->where('status',0)->first();
                    
                    if(empty($platform)){
                        $v->comments = '无可用会员平台';
                        $v->status = 5;
                        $v->update_time = $time;
                        $v->tg_notice_admin = 'Y';
                        $v->save();
                        continue;
                    }
                    
                    $rsa_services = new RsaServices();
                    $signstr = $rsa_services->privateDecrypt($platform->platform_cookie);
                    
                    if(empty($signstr)){
                        $v->comments = '会员平台未配置cookie';
                        $v->status = 5;
                        $v->update_time = $time;
                        $v->tg_notice_admin = 'Y';
                        $v->save();
                        continue;
                    }
                    
                    $phrase = $rsa_services->privateDecrypt($platform->platform_phrase);
                    
                    if(empty($phrase)){
                        $v->comments = '会员平台未配置助记词';
                        $v->status = 5;
                        $v->update_time = $time;
                        $v->tg_notice_admin = 'Y';
                        $v->save();
                        continue;
                    }
                    
                    $this->log('tgpremium',$v->rid.'：下单中，开始调用');
                    
                    $v->comments = '开通中';
                    $v->status = 6;
                    $v->update_time = $time;
                    $v->save();
                    
                    $apiWebUrl = config('services.api_web.url');
                    $ton_url = $apiWebUrl . '/api/ton/premium';
                    $param = '{
                        "username": "'.$v->premium_tg_username.'",
                        "isshow": true,
                        "mnemonic": "'.$phrase.'",
                        "hash_value": "'.$platform->platform_hash.'",
                        "cookie": "'.$signstr.'",
                        "months": "'.$v->premium_package_month.'"
                    }';
                    
                    $headers = [
                        'Content-Type: application/json'
                    ];
                    
                    $lastres = curl_post_https($ton_url,$param,$headers,null,18);
                    
                    $this->log('tgpremium',$v->rid.'：调用结果：'.$lastres);
                    
                    if(empty($lastres)){
                        $v->comments = '最后交易返回空,看是否开通成功';
                        $v->status = 2;
                        $v->complete_time = $time;
                        $v->tg_notice_user = 'Y';
                        $v->tg_notice_admin = 'Y';
                        $v->save();
                        continue;
                        
                    }else{
                        $lastres = json_decode($lastres,true);
                        if($lastres['code'] == 200){
                            $v->comments = '开通成功';
                            $v->status = 2;
                            $v->complete_time = $time;
                            $v->tx_hash = $lastres['data']['txhash'];
                            $v->tg_notice_user = 'Y';
                            $v->save();
                            continue;
                            
                        }else{
                            $v->comments = '开通失败';
                            $v->status = 5;
                            $v->update_time = $time;
                            $v->tg_notice_admin = 'Y';
                            $v->save();
                            continue;
                        }
                    }
                }

            }else{
                // $this->log('tgpremium','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('tgpremium','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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