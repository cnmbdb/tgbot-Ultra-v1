<?php

namespace App\Http\Services;

use App\Http\Services\RsaServices;
use App\Models\Telegram\TelegramBot;
use App\Models\Transit\TransitWallet;
use App\Services\AipHttpClient;

class YuZhiServices
{
    /**
     * 调用转trx
     * @param $data json数据
     */
    public function yuzhisendtrx($request)
    {
        $model = TransitWallet::where('bot_rid',$request['bot_rid'])->where('status',0)->first();

        if(empty($model)){
            return ['code' => '400', 'msg' => '未配置预支的钱包,联系管理员'];
        }
        
        $rsa_services = new RsaServices();
        
        $send_wallet_privatekey = $rsa_services->privateDecrypt($model['send_wallet_privatekey']);
        
        $AipHttpClient = new AipHttpClient();
        
        $params = [
            'pri1' => $send_wallet_privatekey,
            'fromaddress' => $model['send_wallet'],
            'toaddress' => $request['toaddress'],
            'sendamount' => $request['now_yuzhi'],
        ];
        
        $apiWebUrl = config('services.api_web.url');
        $res = $AipHttpClient->postnew($apiWebUrl . '/api/tron/sendtrxbypermid', $params);
        $res = json_decode($res,true);

        if(empty($res)){
             return ['code' => '400', 'msg' => '预支转trx失败1'];
        }else{
            if($res['code'] == 200){
                return ['code' => '200', 'msg' => '预支转账成功'];
            }else{
                return ['code' => '400', 'msg' => '预支转trx失败2'];
            }
        }
        
    }
}