<?php

namespace App\Http\Services;

use App\Http\Services\RsaServices;
use App\Models\Telegram\TelegramBot;
use App\Models\Transit\TransitWallet;
use App\Services\AipHttpClient;

class XiaFaServices
{
    /**
     * 调用转账
     * @param $data json数据
     */
    public function xiafaSend($request)
    {
        $model = TransitWallet::where('bot_rid',$request['bot_rid'])->where('status',0)->first();

        if(empty($model)){
            return ['code' => '400', 'msg' => '未配置预支的钱包,联系管理员'];
        }
        
        if($model['send_wallet'] == $request['toaddress']){
            return ['code' => '400', 'msg' => '地址不能为同一个'];
        }
        
        $rsa_services = new RsaServices();
        
        $send_wallet_privatekey = $rsa_services->privateDecrypt($model['send_wallet_privatekey']);
        
        $AipHttpClient = new AipHttpClient();
        if($request['send_type'] == 'trx'){
            $params = [
                'pri1' => $send_wallet_privatekey,
                'fromaddress' => $model['send_wallet'],
                'toaddress' => $request['toaddress'],
                'sendamount' => $request['send_amount'],
                'permissionid' => 0
            ];
            
            $apiWebUrl = config('services.api_web.url');
            $res = $AipHttpClient->postnew($apiWebUrl . '/api/tron/sendtrxbypermid', $params);
        }else{
            $params = [
                'pri1' => $send_wallet_privatekey,
                'fromaddress' => $model['send_wallet'],
                'toaddress' => $request['toaddress'],
                'sendamount' => $request['send_amount'], //0 means all
                'trc20ContractAddress' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'permissionid' => 0
            ];
            
            $apiWebUrl = config('services.api_web.url');
            $res = $AipHttpClient->postnew($apiWebUrl . '/api/tron/sendtrc20bypermid', $params);
        }
        
        $res = json_decode($res,true);
        
        if(empty($res)){
             return ['code' => '400', 'msg' => '下发失败1'];
        }else{
            if($res['code'] == 200){
                return ['code' => '200', 'msg' => '下发成功'];
            }else{
                return ['code' => '400', 'msg' => '下发失败2,返回：'.json_encode($res)];
            }
        }
        
    }
}