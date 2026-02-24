<?php

namespace App\Http\Services;

use App\Http\Services\RsaServices;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformBot;

class EnergyServices
{
    /**
     * 调用预支能量
     * @param $data json数据
     */
    public function sendenergy($request)
    {
        $pfbotdata = EnergyPlatformBot::where('bot_rid',$request['bot_rid'])->where('status',0)->first();

        if(empty($pfbotdata)){
            return ['code' => '400', 'msg' => '未配置能量平台,联系管理员'];
        }
        
        //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
        $model = EnergyPlatform::where('poll_group',$pfbotdata['poll_group'])
                ->where('status',0)
                ->whereNotNull('platform_apikey')
                ->where(function ($query) use($request) {
                    $query->where(function ($query1) use($request){
                         $query1->where('platform_name', 3)->where('platform_balance', '>=', $request['energy_amount']);
                    });
                    $query->orwhere(function ($query2) {
                         $query2->orwhereIn('platform_name', [1,2,4,5,6])->where('platform_balance', '>', 0);
                     });
                 })
                ->orderBy('seq_sn','desc')
                ->get();
        
        if($model->count() > 0){
            $errorMessage = '';
            foreach ($model as $k => $v){
                $rsa_services = new RsaServices();
                $signstr = $rsa_services->privateDecrypt($v->platform_apikey);
        
                if(empty($signstr)){
                    // return ['code' => '400', 'msg' => '未配置能量平台私钥,联系管理员'];
                    //未配置能量平台私钥,轮询下一个
                    $errorMessage = $errorMessage."能量平台：".$v->rid." 平台私钥为空。";
                    continue;
                }
                
                //neee.cc平台
                if($v->platform_name == 1){
                    $header = [
                        "Content-Type:application/json"
                    ];
                    
                    $param = [
                        "uid" => strval($v->platform_uid),
                        "resource_type" => "0", //0能量
                        "receive_address" => $request['receive_address'],
                        "amount" => strval($request['energy_amount']),
                        "freeze_day" => strval($request['energy_day']), //0：一小时，1：一天，3：三天
                        "time" => strval(time())
                    ];
                    
            		ksort($param);
            		reset($param);
            
            		foreach($param as $k1 => $v1){
            			if($k1 != "sign" && $k1 != "sign_type" && $v1 != ''){
            				$signstr .= $k1.$v1;
            			}
            		}
            		
            		$sign = md5($signstr);
            		$param['sign'] = $sign;
                    $balance_url = 'https://api.tronqq.com/openapi/v2/order/submit';
                    $res = Get_Curl($balance_url,json_encode($param),$header);
                    
                //RentEnergysBot平台
                }elseif($v->platform_name == 2){
                    //0：一小时，1：一天，3：三天
                    switch ($request['energy_day']) {
                        case 1:
                            $type = 'day';
                            break;
                        case 3:
                            $type = '3day';
                            break;
                        default:
                            $type = 'hour';
                            break;
                    }
                    
                    //该平台最低33000
                    $energy_amount = $request['energy_amount'] < 33000 ?33000:$request['energy_amount'];
                    
                    $balance_url = 'https://api.wallet.buzz?api=getEnergy&apikey='.$signstr.'&address='.$request['receive_address'].'&amount='.$energy_amount.'&type='.$type;
                    $res = Get_Curl($balance_url);
                    
                //自己质押
                }elseif($v->platform_name == 3){
                    $params = [
                        'pri' => $signstr,
                        'fromaddress' => $v->platform_uid,
                        'receiveaddress' => $request['receive_address'],
                        'resourcename' => 'ENERGY',
                        'resourceamount' => $request['energy_amount'],
                        'resourcetype' => 1,
                        'permissionid' => $v->permission_id
                    ];
                    
                    $apiWebUrl = config('services.api_web.url');
                    $res = Get_Curl($apiWebUrl . '/api/tron/delegaandundelete',$params);
                    
                //trongas.io平台
                }elseif($v->platform_name == 4){
                    //0：一小时，1：一天，3：三天
                    switch ($request['energy_day']) {
                        case 1:
                            $rentTime = 24;
                            break;
                        case 3:
                            $rentTime = 72;
                            break;
                        default:
                            $rentTime = 1;
                            break;
                    }
                    
                    $param = [
                        "username" => $v->platform_uid, // 用户名
                        "password" => $signstr, // 用户密码
                        "resType" => "ENERGY", // 资源类型，ENERGY：能量，BANDWIDTH：带宽
                        "payNums" => $request['energy_amount'], // 租用数量
                        "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                        "resLock" => 0, // 租用锁定，0：不锁定，1：锁定。能量租用数量不小于500万且租用时间不小于3天才能锁定。带宽租用数量不小于30万租用时间不小于3天才能锁定
                        "receiveAddress" => $request['receive_address'] // 接收资源地址(请勿输入合约地址或没激活地址)
                    ];
                    
                    $balance_url = 'https://trongas.io/api/pay';
                    $res = Get_Curl($balance_url,$param);
                
                //搜狐平台
                }elseif($v->platform_name == 6){
                    //0：一小时，1：一天，3：三天
                    switch ($request['energy_day']) {
                        case 1:
                            $rentTime = '1day';
                            break;
                        case 3:
                            $rentTime = '3day';
                            break;
                        default:
                            $rentTime = '1h';
                            break;
                    }
                    
                    $param = [
                        "token" => $signstr, // 令牌
                        "type" => "energy", // 资源类型，energy:能量  both:能量+带宽
                        "count" => $request['energy_amount'], // 租用数量
                        "period" => $rentTime, // 1h:1小时 1day:1天 3day:3天
                        "trx_amount" => 0, // 选填，带宽手续费，type=both时有效, 比如 0.35
                        "address" => $request['receive_address'] // 接收资源地址(请勿输入合约地址或没激活地址)
                    ];
                    
                    $balance_url = 'https://admin.weidubot.cc/api/trc_api/frozen_energy';
                    $res = Get_Curl($balance_url,$param);
                    
                //机器人开发者代理
                }elseif($v->platform_name == 5){
                    $balance_url = env('THIRD_URL');
                    if(empty($balance_url)){
                        $errorMessage = $errorMessage."使用开发者能量代理时,env中url为空";
                        continue;
                    }
                    
                    //0：一小时，1：一天，3：三天
                    switch ($request['energy_day']) {
                        case 1:
                            $rentTime = 24;
                            break;
                        case 3:
                            $rentTime = 72;
                            break;
                        default:
                            $rentTime = 1;
                            break;
                    }
                    //使用开发者能量代理,仅支持65000 65000能量代理1小时
                    if($rentTime != 1){
                        $errorMessage = $errorMessage."使用开发者能量代理时,时长只能为1小时";
                        continue;
                    }
                    
                    $param = [
                        "tg_uid" => $v->platform_uid, // 用户名,此处是tg的uid,因为是机器人能量代理模式
                        "payNums" => $request['energy_amount'], // 租用数量
                        "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                        "receiveAddress" => $request['receive_address'] // 接收资源地址(请勿输入合约地址或没激活地址)
                    ];
                    
                    $balance_url = $balance_url.'/api/thirdpart/shanzuorder';
                    $res = Get_Curl($balance_url,$param);
                    
                //NL-API平台（tgnl-home能量池系统）
                }elseif($v->platform_name == 7){
                    // 获取tgnl-home域名，优先从环境变量，其次从comments字段
                    $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
                    if(empty($nlApiBaseUrl) && !empty($v->comments)){
                        // 尝试从comments中解析域名（格式：nl_api_url=https://xxx.com）
                        if(preg_match('/nl_api_url=([^\s]+)/i', $v->comments, $matches)){
                            $nlApiBaseUrl = trim($matches[1]);
                        }
                    }
                    
                    if(empty($nlApiBaseUrl)){
                        $errorMessage = $errorMessage."能量平台：".$v->rid." NL-API域名未配置。";
                        continue;
                    }
                    
                    // platform_uid 作为 API username
                    $apiUsername = $v->platform_uid;
                    // platform_apikey 解密后作为 API password
                    $apiPassword = $rsa_services->privateDecrypt($v->platform_apikey);
                    
                    if(empty($apiUsername) || empty($apiPassword)){
                        $errorMessage = $errorMessage."能量平台：".$v->rid." NL-API账户或密码未配置。";
                        continue;
                    }
                    
                    // 转换天数：0=1小时，1=1天，3=3天
                    $day = $request['energy_day'];
                    if($day == 0){
                        $day = 0; // 1小时
                    }elseif($day == 1){
                        $day = 1; // 1天
                    }elseif($day == 3){
                        $day = 3; // 3天
                    }else{
                        $day = 0; // 默认1小时
                    }
                    
                    $param = [
                        'username' => $apiUsername,
                        'password' => $apiPassword,
                        'energy' => $request['energy_amount'],
                        'day' => $day,
                        'receiver_address' => $request['receive_address']
                    ];
                    
                    $balance_url = rtrim($nlApiBaseUrl, '/') . '/v1/delegate_meal';
                    $header = [
                        "Content-Type: application/json",
                        "Accept: application/json"
                    ];
                    $res = Get_Curl($balance_url, json_encode($param), $header);
                    
                }else{
                    // return ['code' => '400', 'msg' => '能量平台不存在,联系管理员'];
                    //能量平台接口不存在,轮询下一个
                    $errorMessage = $errorMessage."能量平台：".$v->rid." 能量平台接口不存在。";
                    continue;
                }
                
                if(empty($res)){
                    // return ['code' => '400', 'msg' => '代理能量失败1,接口为空'];
                    //能量平台接口返回为空,轮询下一个
                    $errorMessage = $errorMessage."能量平台：".$v->rid." 能量平台接口返回为空。";
                    continue;
                }else{
                    $res = json_decode($res,true);
                    if((isset($res['status']) && $res['status'] == 200 && $v->platform_name == 1) || (isset($res['status']) && $res['status'] == 'success' && $v->platform_name == 2) || (isset($res['code']) && $res['code'] == 200 && $v->platform_name == 3) || (isset($res['code']) && $res['code'] == 10000 && $v->platform_name == 4) || (isset($res['code']) && $res['code'] == 200 && $v->platform_name == 5) || (isset($res['code']) && $res['code'] == 1 && $v->platform_name == 6) || (($v->platform_name == 7 && (isset($res['success']) && $res['success'] === true) || isset($res['tx_hash']) || isset($res['txHash'])))){
                        if($v->platform_name == 1){
                            $data['orderNo'] = $res['data']['order_no'];
                            $data['use_trx'] = 0;
                            
                        }elseif($v->platform_name == 2){
                            $data['orderNo'] = $res['txid'];
                            $data['use_trx'] = 0;
                            
                        }elseif(isset($res['data']['txid']) && $v->platform_name == 3){
                            $data['orderNo'] = $res['data']['txid'];
                            $data['use_trx'] = $res['data']['use_trx'];
                            
                        }elseif(isset($res['data']['orderId']) && $v->platform_name == 4){
                            $data['orderNo'] = $res['data']['orderId'];
                            $data['use_trx'] = $res['data']['orderMoney'];
                            
                        }elseif($v->platform_name == 5){
                            $data['orderNo'] = $res['data']['orderId'];
                            $data['use_trx'] = $res['data']['orderMoney'];
                            
                        }elseif($v->platform_name == 6){
                            $data['orderNo'] = $res['data']['order_sn'];
                            $data['use_trx'] = $res['data']['amount'];
                            
                        }elseif($v->platform_name == 7){
                            // NL-API返回的订单号（可能是tx_hash或其他标识）
                            $data['orderNo'] = $res['tx_hash'] ?? $res['txHash'] ?? $res['order_id'] ?? 'NL-'.time();
                            $data['use_trx'] = 0; // NL-API平台内部扣费，这里不记录TRX
                            
                        }else{
                            $data['orderNo'] = '未知'.time();
                            $data['use_trx'] = 0;
                        }
                        $data['energy_platform_rid'] = $v->rid;
                        $data['energy_platform_bot_rid'] = $pfbotdata['rid'];
                        $data['platform_name'] = $v->platform_name;
                        $data['platform_uid'] = $v->platform_uid;
                        return ['code' => '200', 'msg' => '预支能量成功', 'data' => $data];
                    }else{
                        if($v->platform_name == 1){
                            $msg = '失败,返回:'.$res['msg'];
                        }elseif($v->platform_name == 2){
                            $msg = '失败,返回:'.json_encode($res);
                        }elseif($v->platform_name == 3){
                            $msg = '失败,检查质押是否足够';
                        }elseif($v->platform_name == 4){
                            $msg = '失败,返回:'.json_encode($res);
                        }elseif($v->platform_name == 5){
                            $msg = '失败,返回:'.json_encode($res);
                        }elseif($v->platform_name == 6){
                            $msg = '失败,返回:'.json_encode($res);
                        }elseif($v->platform_name == 7){
                            $msg = '失败,返回:'.(isset($res['error']) ? $res['error'] : json_encode($res));
                        }
                        // return ['code' => '400', 'msg' => $msg];
                        //能量平台下单失败,轮询下一个
                        $errorMessage = $errorMessage."能量平台：".$v->rid.$msg;
                        continue;
                    }
                }
            }
            return ['code' => '400', 'msg' => '机器人所有能量平台失败：'.$errorMessage];
        }else{
            return ['code' => '400', 'msg' => '机器人无可用能量平台,请质押或者充值平台'];
        }
    }
}