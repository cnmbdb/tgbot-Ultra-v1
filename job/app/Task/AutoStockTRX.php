<?php
namespace App\Task;

use App\Model\Transit\TransitWallet;
use App\Service\RsaServices;
use App\Library\Log;

class AutoStockTRX
{
    public function execute()
    { 
        try {
            $data = TransitWallet::from('t_transit_wallet as a')
                    ->Join('t_telegram_bot as b','a.bot_rid','b.rid')
                    ->where('a.status',0)
                    ->where('a.auto_stock_min_trx','>',0)
                    ->where('a.auto_stock_per_usdt','>',0)
                    ->whereNotNull('a.send_wallet')
                    ->whereRaw('length(a.send_wallet) = 34')
                    ->select('a.rid','a.send_wallet','a.send_wallet_privatekey','a.auto_stock_min_trx','a.auto_stock_per_usdt','a.tg_notice_obj_receive','b.bot_token')
                    ->get();
            
            if($data->count() > 0){
                foreach ($data as $k => $v) {
                    $tronsuccess = 1;
                    $trxbalance = 0;
                    $usdtbalance = 0;
                    sleep(1); //不容易被api限制
                    
                    #查钱包余额
                    if(!empty($v->send_wallet) && $v->send_wallet != ''){
                        $tronurl = 'https://apilist.tronscanapi.com/api/account/tokens?address='.$v->send_wallet.'&start=0&limit=200&hidden=0&show=0&sortType=0&sortBy=0&token='; //查波场地址余额,通过http接口查询
                        
                        $api_key = config('apikey.tronapikey');
                        $apikeyrand = $api_key[array_rand($api_key)];
                        
                        $heders = [
                            "TRON-PRO-API-KEY:".$apikeyrand
                        ];
                        
                        $tronres = Get_Pay($tronurl,null,$heders);

                        if(empty($tronres)){
                            $this->log('autostocktrx','自动进货余额获取错误1:'.$v->rid);
                        }else{
                            $tronres = json_decode($tronres,true);
                            if(empty($tronres['data'])){
                                $this->log('autostocktrx','自动进货余额获取错误2:'.$v->rid);
                            }else{
                                $tronsuccess = 2;

                                for($i = 0; $i < $tronres['total']; $i++) {
                                    $tokeninfo = $tronres['data'][$i];

                                    if($tokeninfo['tokenId'] == '_' && $tokeninfo['tokenAbbr'] == 'trx'){
                                        $trxbalance = $tokeninfo['quantity'] == '0.000000' ? 0 : $tokeninfo['quantity'];
                                    }elseif($tokeninfo['tokenId'] == 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'){
                                        $usdtbalance = $tokeninfo['quantity'];
                                        break; //找到usdt就跳出
                                    }
                                }
                                
                                $this->log('autostocktrx',$v->send_wallet.' 自动进货余额获取成功1，USDT余额：'.$usdtbalance.' TRX余额：'.$trxbalance);
                            }
                        }
                        
                        //如果查询失败，则查trongrid接口
                        if($tronsuccess == 1){
                            #查usdt或者余额是否足够
                            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$v->send_wallet;      //查地址
                            $tronapikey = config('apikey.gridapikey');
                            $apikeyrand = $tronapikey[array_rand($tronapikey)];
                            
                            $heders = [
                                "TRON-PRO-API-KEY:".$apikeyrand
                            ];
                            
                            $res = Get_Pay($balance_url,null,$heders);
                            
                            if(empty($res)){
                                $this->log('autostocktrx','自动进货余额获取错误3:'.$v->rid);
                            }else{
                                $res = json_decode($res,true);
                                if(isset($res['success']) && $res['success']){
                                    if(empty($res['data'])){
                                        $this->log('autostocktrx','自动进货余额获取错误4:'.$v->rid);
                                    }else{
                                        $tronsuccess = 2;
                                        
                                        $trxbalance = empty($res['data'][0]['balance']) ? 0 : bcdiv($res['data'][0]['balance'],1000000,6) + 0;
                                        $usdtbalance = 0;
                                        
                                        if(!empty($res['data'][0]['trc20'])){
                                            for($i=1; $i<=count($res['data'][0]['trc20']); $i++){
                                                if(!empty($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'])){
                                                    $usdtbalance = bcdiv($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'],1000000,6) + 0;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        $this->log('autostocktrx',$v->send_wallet.' 自动进货余额获取成功2，USDT余额：'.$usdtbalance.' TRX余额：'.$trxbalance);
                                    }
                                }else{
                                    $this->log('autostocktrx','自动进货余额获取错误5:'.$v->rid);
                                }
                            }
                        }
                    }
                    
                    //余额请求成功，trx余额小于trx自动进货金额，trx余额大于等于50(闪兑需要的手续费)，usdt大于每次闪兑的数量
                    if($tronsuccess == 2 && $usdtbalance >= $v->auto_stock_per_usdt && $trxbalance < $v->auto_stock_min_trx && $trxbalance >= 50){
                        #余额足够则闪兑
                        $rsa_services = new RsaServices();
                        $send_wallet_privatekey = $rsa_services->privateDecrypt($v->send_wallet_privatekey);
                        
                        $apiWebUrl = config('services.api_web.url');
                        $swapurl = $apiWebUrl . '/api/tron/swap'; //swap api (需要实现)
                        $swapparams = [
                            'address' => $v->send_wallet,
                            'swapamount' => $v->auto_stock_per_usdt,
                            'swapplatform' => 'sunswap',
                            'pri1' => $send_wallet_privatekey,
                            'pri2' => '',
                            'pri3' => '',
                            'pri4' => '',
                            'pri5' => ''
                        ];
                        $swapres = Get_Pay($swapurl, $swapparams);
                        
                        if(empty($swapres)){
                            $this->log('autostocktrx','闪兑错误1:'.$v->rid);
                        }else{
                            $swapres = json_decode($swapres,true);
                            if(empty($swapres['code'])){
                                $this->log('autostocktrx','闪兑错误2:'.$v->rid);
                            }else{
                                if($swapres['code'] == 200){
                                    $this->log('autostocktrx',$v->send_wallet.' 闪兑成功');
                                    
                                    //发送tg通知
                                    if(!empty($v->tg_notice_obj_receive)){
                                        $replytext = "✅自动进货成功\n"
                                            ."---------------------------------------\n"
                                            ."进货钱包：<code>".$v->send_wallet ."</code>\n"
                                            ."进货USDT金额：".$v->auto_stock_per_usdt ."\n"
                                            ."---------------------------------------";
                                            
                                        $receivelist = explode(',',$v->tg_notice_obj_receive);
                                        foreach ($receivelist as $x => $y) {
                                            $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                
                                            Get_Pay($sendmessageurl);
                                        }
                                    }
                                }else{
                                    $this->log('autostocktrx','闪兑错误3:'.$v->rid.'。错误：'.$swapres['msg']);
                                }
                            }
                        }
                    }
                }
            }
            
        }catch (\Exception $e){
            $this->log('autostocktrx','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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