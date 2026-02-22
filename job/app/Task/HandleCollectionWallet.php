<?php
namespace App\Task;

use App\Model\Collection\CollectionWalletList;
use App\Model\Collection\CollectionWallet;
use App\Library\Log;
use App\Service\RsaServices;
use App\Model\Transit\TransitWallet;

class HandleCollectionWallet
{
    public function execute()
    { 
        try {
            $data = CollectionWallet::from('t_collection_wallet as a')
                    ->Join('t_telegram_bot as b','a.bot_rid','b.rid')
                    ->where('a.status',0)
                    ->whereNotNull('a.wallet_addr')
                    ->whereRaw('length(a.wallet_addr) = 34')
                    ->select('a.rid','a.wallet_addr','a.wallet_addr_privatekey','a.trx_collection_amount','a.usdt_collection_amount','a.trx_reserve_amount','a.usdt_reserve_amount','a.collection_wallet_addr','a.permission_id','a.tg_notice_obj','b.bot_token','a.bot_rid')
                    ->get();
            
            if($data->count() > 0){
                foreach ($data as $k => $v) {
                    $tronsuccess = 1;
                    $trxbalance = 0;
                    $usdtbalance = 0;
                    $bandwidth = 0;
                    $energy = 0;
                    sleep(1); //不容易被api限制
                    
                    #查钱包余额
                    if(!empty($v->wallet_addr) && $v->wallet_addr != ''){
                        $url = 'https://apilist.tronscanapi.com/api/accountv2?address='.$v->wallet_addr;
                        
                        $api_key = config('apikey.tronapikey');
                        $apikeyrand = $api_key[array_rand($api_key)];
                        $heders = [
                            "TRON-PRO-API-KEY:".$apikeyrand
                        ];
                        
                        $res = Get_Pay($url,null,$heders);

                        if(empty($res)){
                            $this->log('handlecollectionwallet','归集钱包余额获取错误1:'.$v->rid);
                        }else{
                            $res = json_decode($res,true);
                            //查询余额
                            if(isset($res['withPriceTokens'])){
                                $tronsuccess = 2;
                                
                                $withPriceTokens = $res['withPriceTokens'];
                                $trxkey = array_search('_', array_column($withPriceTokens, 'tokenId'));
                                $usdtkey = array_search('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', array_column($withPriceTokens, 'tokenId'));
                                if($trxkey >= 0){
                                    $trxbalance = $withPriceTokens[$trxkey]['amount'] + 0;
                                }
                                
                                if(is_bool($usdtkey)){
                                    //赋值0
                                }else{
                                    $usdtbalance = calculationExcept($withPriceTokens[$usdtkey]['balance'] ,$withPriceTokens[$usdtkey]['tokenDecimal']);
                                }
                                
                                //获取资源,下面转账用
                                if(isset($res['bandwidth'])){
                                    //只处理激活的地址,未激活不能代理
                                    $active = $res['activated'] ?"Y":"N";
                                    if($active == 'Y'){
                                        $bandwidth = $res['bandwidth']['freeNetRemaining'] + $res['bandwidth']['netRemaining'];
                                        $energy = $res['bandwidth']['energyRemaining'];
                                    }
                                }
                                
                                $this->log('handlecollectionwallet',$v->wallet_addr.' 归集钱包余额获取成功1，USDT余额：'.$usdtbalance.' TRX余额：'.$trxbalance);
                            }
                        }
                        
                        //如果查询失败，则查trongrid接口
                        if($tronsuccess == 1){
                            #查usdt或者余额是否足够
                            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$v->wallet_addr;      //查地址
                            $tronapikey = config('apikey.gridapikey');
                            $apikeyrand = $tronapikey[array_rand($tronapikey)];
                            
                            $heders = [
                                "TRON-PRO-API-KEY:".$apikeyrand
                            ];
                            
                            $res = Get_Pay($balance_url,null,$heders);
                            
                            if(empty($res)){
                                $this->log('handlecollectionwallet','归集钱包余额获取错误3:'.$v->rid);
                            }else{
                                $res = json_decode($res,true);
                                if(isset($res['success']) && $res['success']){
                                    if(empty($res['data'])){
                                        $this->log('handlecollectionwallet','归集钱包余额获取错误4:'.$v->rid);
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
                                        
                                        $this->log('handlecollectionwallet',$v->wallet_addr.' 归集钱包余额获取成功2，USDT余额：'.$usdtbalance.' TRX余额：'.$trxbalance);
                                    }
                                }else{
                                    $this->log('handlecollectionwallet','归集钱包余额获取错误5:'.$v->rid);
                                }
                            }
                            
                            //获取成功的情况再去查资源
                            if($tronsuccess == 2){
                                $balance_url = 'https://api.trongrid.io/wallet/getaccountresource';
                                $tronapikey = config('apikey.gridapikey');
                                $apikeyrand = $tronapikey[array_rand($tronapikey)];
                                
                                $heders = [
                                    "TRON-PRO-API-KEY:".$apikeyrand
                                ];
                                
                                $param = [
                                    "address" => $v->wallet_addr,
                                    "visible" => true
                                ];
                                
                                $res2 = curl_post_https($balance_url,json_encode($param),$heders);
                                
                                if(empty($res2)){
                                    $this->log('handlecollectionwallet','归集钱包资源获取错误1:'.$v->rid);
                                }else{
                                    $res2 = json_decode($res2,true);
                                    if(isset($res2['freeNetLimit'])){
                                        $bandwidth = ($res2['NetLimit'] ?? 0) + ($res2['freeNetLimit'] ?? 0) - ($res2['NetUsed'] ?? 0) ;
                                        $energy = ($res2['EnergyLimit'] ?? 0) - ($res2['EnergyUsed'] ?? 0);
                                        
                                    }else{
                                        $this->log('handlecollectionwallet','归集钱包资源获取错误2:'.$v->rid);
                                    }
                                }
                            }
                        }
                    }
                    
                    //更新余额
                    if($tronsuccess == 2){
                        CollectionWallet::where('rid',$v->rid)->update(['trx_balance' => $trxbalance, 'usdt_balance' => $usdtbalance]);
                    }
                    
                    //余额请求成功，处理usdt是否归集
                    if($tronsuccess == 2 && $usdtbalance >= $v->usdt_collection_amount && $v->usdt_collection_amount - $v->usdt_reserve_amount > 0 && !empty($v->collection_wallet_addr) && mb_strlen($v->collection_wallet_addr) == 34 && $v->usdt_collection_amount > 0){
                        $rsa_services = new RsaServices();
                        $is_can = 'N';
                        
                        $this->log('handlecollectionwallet','====开始归集USDT：'.$v->wallet_addr."。查询当前钱包USDT余额：".$usdtbalance);
                        
                        //判断转账资源是否足够
                        if(($energy >= 31895 && ($bandwidth >= 345 || $trxbalance >= 0.35)) || ($trxbalance >= 13.75)){
                            //足够转账不处理
                            $is_can = 'Y';
                        }else{
                            $this->log('handlecollectionwallet','====能量不足，执行从闪兑钱包转入15 TRX：');
                            
                            //不足则要从机器人闪兑钱包转trx
                            $transitwallet = TransitWallet::where('bot_rid',$v->bot_rid)->whereNotNull('send_wallet_privatekey')->whereRaw('length(send_wallet) = 34')->first();
                            if(empty($transitwallet)){
                                continue; //查不到机器人的闪兑钱包,则不处理USDT转账
                            }else{
                                $send_wallet_privatekey = $rsa_services->privateDecrypt($transitwallet->send_wallet_privatekey);
                                if(empty($send_wallet_privatekey)){
                                    continue; //私钥为空也不处理
                                }else{
                                    //从闪兑钱包转15个trx
                                    $apiWebUrl = config('services.api_web.url');
                                    $url = $apiWebUrl . '/api/tron/sendtrxbypermid'; //trx api
                                    $params = [
                                        'fromaddress' => $transitwallet->send_wallet,
                                        'toaddress' => $v->wallet_addr,
                                        'sendamount' => 15,
                                        'pri1' => $send_wallet_privatekey,
                                        'permissionid' => 0
                                    ];
                                    $res = Get_Pay($url, $params);
                                    
                                    if(empty($res)){
                                        $this->log('handlecollectionwallet','转入trx矿工费失败1:'.$v->rid);
                                    }else{
                                        $res = json_decode($res,true);
                                        if(empty($res['code'])){
                                            $this->log('handlecollectionwallet','转入trx矿工费失败2:'.$v->rid);
                                        }else{
                                            if($res['code'] == 200){
                                                $this->log('handlecollectionwallet',$v->wallet_addr.' 转入trx矿工费成功');
                                                $is_can = 'Y';
                                                sleep(3); //成功的情况,等3秒,链上确认
                                            }else{
                                                $this->log('handlecollectionwallet','转入trx矿工费失败3:'.$v->rid.'。错误：'.$res['msg']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        if($is_can == 'Y'){
                            $TransAmount = $usdtbalance - $v->usdt_reserve_amount; //归集的金额
                            
                            $this->log('handlecollectionwallet','====转出USDT钱包：'.$v->wallet_addr."。归集钱包：".$v->collection_wallet_addr."。归集金额：".$TransAmount."。设置的归集金额：".$v->usdt_collection_amount."。设置的预留金额：".$v->usdt_reserve_amount);
                            
                            $wallet_addr_privatekey = $rsa_services->privateDecrypt($v->wallet_addr_privatekey);
                            
                            $apiWebUrl = config('services.api_web.url');
                            $url = $apiWebUrl . '/api/tron/sendtrc20bypermid'; //usdt api
                            $params = [
                                'fromaddress' => $v->wallet_addr,
                                'toaddress' => $v->collection_wallet_addr,
                                'sendamount' => $TransAmount,
                                'trc20ContractAddress' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                                'pri1' => $wallet_addr_privatekey,
                                'permissionid' => $v->permission_id
                            ];
                            $res = Get_Pay($url, $params);
                            
                            if(empty($res)){
                                $this->log('handlecollectionwallet','归集USDT错误1:'.$v->rid);
                            }else{
                                $res = json_decode($res,true);
                                if(empty($res['code'])){
                                    $this->log('handlecollectionwallet','归集USDT错误2:'.$v->rid);
                                }else{
                                    if($res['code'] == 200){
                                        $this->log('handlecollectionwallet',$v->wallet_addr.' 归集USDT成功');
                                        
                                        //记录归集明细
                                        $insert_date = [];
                                        $insert_date['wallet_addr'] = $v->wallet_addr;
                                        $insert_date['collection_wallet_addr'] = $v->collection_wallet_addr;
                                        $insert_date['coin_name'] = 'usdt';
                                        $insert_date['collection_amount'] = $TransAmount;
                                        $insert_date['collection_time'] = nowDate();
                                        $insert_date['tx_hash'] = $res['data']['txid'];
                                        CollectionWalletList::insert($insert_date);
                                        
                                        //发送tg通知
                                        if(!empty($v->tg_notice_obj)){
                                            $replytext = "✅钱包归集成功\n"
                                                ."---------------------------------------\n"
                                                ."钱包地址：<code>".$v->wallet_addr ."</code>\n"
                                                ."归集地址：".$v->collection_wallet_addr ."\n"
                                                ."归集金额：".$TransAmount." USDT\n"
                                                ."---------------------------------------";
                                                
                                            $receivelist = explode(',',$v->tg_notice_obj);
                                            foreach ($receivelist as $x => $y) {
                                                $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                    
                                                Get_Pay($sendmessageurl);
                                            }
                                        }
                                    }else{
                                        $this->log('handlecollectionwallet','归集USDT错误3:'.$v->rid.'。错误：'.$res['msg']);
                                    }
                                }
                            }
                        }
                    }
                    
                    //余额请求成功，处理trx是否归集
                    if($tronsuccess == 2 && $trxbalance >= $v->trx_collection_amount && $v->trx_collection_amount - $v->trx_reserve_amount - 1.5 > 0 && !empty($v->collection_wallet_addr) && mb_strlen($v->collection_wallet_addr) == 34 && $v->trx_collection_amount > 0){
                        $TransAmount = $trxbalance - $v->trx_reserve_amount - 1.5; //归集的金额,减去1.5个手续费
                        
                        $this->log('handlecollectionwallet','====开始归集TRX：'.$v->wallet_addr."。查询当前钱包TRX余额：".$trxbalance);
                        $this->log('handlecollectionwallet','====转出TRX钱包：'.$v->wallet_addr."。归集钱包：".$v->collection_wallet_addr."。归集金额：".$TransAmount."。设置的归集金额：".$v->trx_collection_amount."。设置的预留金额：".$v->trx_reserve_amount);
                        
                        #余额足够则闪兑
                        $rsa_services = new RsaServices();
                        $wallet_addr_privatekey = $rsa_services->privateDecrypt($v->wallet_addr_privatekey);
                        
                        $apiWebUrl = config('services.api_web.url');
                        $params = [
                            'fromaddress' => $v->wallet_addr,
                            'toaddress' => $v->collection_wallet_addr,
                            'sendamount' => $TransAmount,
                            'pri1' => $wallet_addr_privatekey,
                            'permissionid' => $v->permission_id
                        ];
                        $res = Get_Pay($apiWebUrl . '/api/tron/sendtrxbypermid', $params);
                        
                        if(empty($res)){
                            $this->log('handlecollectionwallet','归集TRX错误1:'.$v->rid);
                        }else{
                            $res = json_decode($res,true);
                            if(empty($res['code'])){
                                $this->log('handlecollectionwallet','归集TRX错误2:'.$v->rid);
                            }else{
                                if($res['code'] == 200){
                                    $this->log('handlecollectionwallet',$v->wallet_addr.' 归集TRX成功');
                                    
                                    //记录归集明细
                                    $insert_date = [];
                                    $insert_date['wallet_addr'] = $v->wallet_addr;
                                    $insert_date['collection_wallet_addr'] = $v->collection_wallet_addr;
                                    $insert_date['coin_name'] = 'trx';
                                    $insert_date['collection_amount'] = $TransAmount;
                                    $insert_date['collection_time'] = nowDate();
                                    $insert_date['tx_hash'] = $res['data']['txid'];
                                    CollectionWalletList::insert($insert_date);
                                    
                                    //发送tg通知
                                    if(!empty($v->tg_notice_obj)){
                                        $replytext = "✅钱包归集成功\n"
                                            ."---------------------------------------\n"
                                            ."钱包地址：<code>".$v->wallet_addr ."</code>\n"
                                            ."归集地址：".$v->collection_wallet_addr ."\n"
                                            ."归集金额：".$TransAmount." TRX\n"
                                            ."---------------------------------------";
                                            
                                        $receivelist = explode(',',$v->tg_notice_obj);
                                        foreach ($receivelist as $x => $y) {
                                            $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML';
                                
                                            Get_Pay($sendmessageurl);
                                        }
                                    }
                                }else{
                                    $this->log('handlecollectionwallet','归集TRX错误3:'.$v->rid.'。错误：'.$res['msg']);
                                }
                            }
                        }
                    }
                }
            }
            
        }catch (\Exception $e){
            $this->log('handlecollectionwallet','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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