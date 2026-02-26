<?php
namespace App\Task;

use App\Model\Energy\EnergyWalletTradeList;
use App\Model\Energy\EnergyPlatformPackage;
use App\Model\Energy\EnergyPlatformOrder;
use App\Model\Energy\EnergyPlatform;
use App\Model\Energy\EnergyAiBishu;
use App\Model\Energy\EnergyThirdPart;
use App\Model\Energy\EnergyQuickOrder;
use App\Model\Telegram\TelegramBotUser;
use App\Service\RsaServices;
use App\Library\Log;

class HandleEnergyOrder
{
    public function execute()
    { 
        //trx闪租能量
        try {
            $data = EnergyWalletTradeList::from('t_energy_wallet_trade_list as a')
                ->join('t_energy_platform_bot as b','a.transferto_address','b.receive_wallet')
                ->where('a.process_status',1)
                ->where('a.coin_name','trx')
                ->select('a.rid','a.transferfrom_address','a.amount','b.poll_group','b.status','b.bot_rid','b.rid as energy_platform_bot_rid','b.agent_tg_uid')
                ->limit(100)
                ->get();
                    
            if($data->count() > 0){
                $time = nowDate();
                
                foreach ($data as $k => $v) {
                    if($v->status == 1){
                        $save_data = [];
                        $save_data['process_status'] = 6;  //能量钱包未启用
                        $save_data['process_comments'] = '能量钱包未启用';      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                        EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                        continue;
                    }
                    
                    //匹配金额
                    $res = EnergyPlatformPackage::where('bot_rid',$v->bot_rid)->where('trx_price',$v->amount)->first();
                    if(empty($res)){
                        $save_data = [];
                        $save_data['process_status'] = 7;  //金额无对应套餐
                        $save_data['process_comments'] = '金额无对应套餐';      //处理备注
                        $save_data['process_time'] = $time;      //处理时间
                        $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                        EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                        continue;
                    }
                    
                    $isAgent = 'N';
                    //如果是代理
                    if(!empty($v->agent_tg_uid)){
                        if(empty($res->agent_trx_price) || $res->agent_trx_price <= 0){
                            $save_data = [];
                            $save_data['process_status'] = 4;
                            $save_data['process_comments'] = '套餐对应代理trx价格为0,无法扣款';      //处理备注
                            $save_data['process_time'] = $time;      //处理时间
                            $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                            EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                        
                        $agentUser = TelegramBotUser::where('tg_uid',$v->agent_tg_uid)->where('bot_rid',$v->bot_rid)->first();
                        if(empty($agentUser)){
                            $save_data = [];
                            $save_data['process_status'] = 4;
                            $save_data['process_comments'] = '代理用户未关注该机器人,无法扣款';      //处理备注
                            $save_data['process_time'] = $time;      //处理时间
                            $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                            EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                            continue;
                        
                        }elseif($agentUser->cash_trx < $res->agent_trx_price){
                            $save_data = [];
                            $save_data['process_status'] = 4;
                            $save_data['process_comments'] = "代理地址对应用户TRX余额不足,无法扣款,需要：".($res->agent_trx_price + 0).",用户余额：".($agentUser->cash_trx + 0);      //处理备注
                            $save_data['process_time'] = $time;      //处理时间
                            $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                            EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                            continue;
                        }elseif($agentUser->cash_trx >= $res->agent_trx_price){
                            $isAgent = 'Y';
                        }else{
                            $save_data = [];
                            $save_data['process_status'] = 4;
                            $save_data['process_comments'] = "代理校验未知错误";      //处理备注
                            $save_data['process_time'] = $time;      //处理时间
                            $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                            EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                    }
                    
                    $energy_amount = $res->energy_amount;
                    // 轮询:
                    // - 自己质押(3): 判断能量是否足够（platform_balance >= energy_amount）
                    // - 其它平台(1,2,4,5,6,7): 判断平台 TRX 余额是否 > 0（包括 NL-API）
                    $model = EnergyPlatform::where('poll_group',$v->poll_group)
                            ->where('status',0)
                            ->whereNotNull('platform_apikey')
                            ->where(function ($query) use($energy_amount) {
                                $query->where(function ($query1) use($energy_amount){
                                     $query1->where('platform_name', 3)->where('platform_balance', '>=', $energy_amount);
                                });
                                $query->orwhere(function ($query2) {
                                     // 1=Neee.cc,2=RentEnergysBot,4=trongas.io,5=开发者代理,6=搜狐,7=NL-API
                                     $query2->orwhereIn('platform_name', [1,2,4,5,6,7])->where('platform_balance', '>', '0');
                                 });
                             })
                            ->orderBy('seq_sn','desc')
                            ->get();
                    
                    if($model->count() > 0){
                        $errorMessage = '';
                        $rsa_services = new RsaServices();
                        
                        foreach ($model as $k1 => $v1){
                            $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                            
                            if(empty($signstr)){
                                // $save_data = [];
                                // $save_data['process_status'] = 5;  //能量钱包未配置私钥
                                // $save_data['process_comments'] = '能量钱包未配置私钥2';      //处理备注  
                                // $save_data['process_time'] = $time;      //处理时间
                                // $save_data['energy_platform_rid'] = $v1->rid;
                                // $save_data['energy_package_rid'] = $res['rid'];
                                // $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                // EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 平台私钥为空。";
                                $save_data = [];
                                $save_data['process_status'] = 5;      //下单失败
                                $save_data['process_comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                $save_data['energy_platform_rid'] = $v1->rid;
                                $save_data['energy_package_rid'] = $res['rid'];
                                $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                            
                            $save_data = [];
                            $save_data['process_status'] = 8;      //下单中
                            $save_data['process_comments'] = '下单中';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            $save_data['energy_platform_rid'] = $v1->rid;
                            $save_data['energy_package_rid'] = $res['rid'];
                            $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                            EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                            
                            $energy_day = $res['energy_day'];
                            //neee.cc平台
                            if($v1->platform_name == 1){
                                $header = [
                                    "Content-Type:application/json"
                                ];
                                $param = [
                                    "uid" => strval($v1->platform_uid),
                                    "resource_type" => "0", //0能量
                                    "receive_address" => $v->transferfrom_address,
                                    "amount" => strval($energy_amount),
                                    "freeze_day" => strval($energy_day), //0：一小时，1：一天，3：三天
                                    "time" => strval(time())
                                ];
                                
                        		ksort($param);
                        		reset($param);
                        
                        		foreach($param as $ka => $va){
                        			if($ka != "sign" && $ka != "sign_type" && $va!=''){
                        				$signstr .= $ka.$va;
                        			}
                        		}
                        		
                        		$sign = md5($signstr);
                        		$param['sign'] = $sign;
                                $balance_url = 'https://api.tronqq.com/openapi/v2/order/submit';
                                $dlres = Get_Pay($balance_url,json_encode($param),$header);
                            }
                            //RentEnergysBot平台
                            elseif($v1->platform_name == 2){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                $energy_amount = $energy_amount < 33000 ?33000:$energy_amount;
                    
                                $balance_url = 'https://api.wallet.buzz?api=getEnergy&apikey='.$signstr.'&address='.$v->transferfrom_address.'&amount='.$energy_amount.'&type='.$type;
                                $dlres = Get_Pay($balance_url);
                            }
                            //自己质押代理
                            elseif($v1->platform_name == 3){
                                $params = [
                                    'pri' => $signstr,
                                    'fromaddress' => $v1->platform_uid,
                                    'receiveaddress' => $v->transferfrom_address,
                                    'resourcename' => 'ENERGY',
                                    'resourceamount' => $energy_amount,
                                    'resourcetype' => 1,
                                    'permissionid' => $v1->permission_id
                                ];
                                $apiWebUrl = config('services.api_web.url');
                                $dlres = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete',$params);
                            //trongas.io平台
                            }elseif($v1->platform_name == 4){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "username" => $v1->platform_uid, // 用户名
                                    "password" => $signstr, // 用户密码
                                    "resType" => "ENERGY", // 资源类型，ENERGY：能量，BANDWIDTH：带宽
                                    "payNums" => $energy_amount, // 租用数量
                                    "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                                    "resLock" => 0, // 租用锁定，0：不锁定，1：锁定。能量租用数量不小于500万且租用时间不小于3天才能锁定。带宽租用数量不小于30万租用时间不小于3天才能锁定
                                    "receiveAddress" => $v->transferfrom_address // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://trongas.io/api/pay';
                                $dlres = Get_Pay($balance_url,$param);
                            //搜狐平台
                            }elseif($v1->platform_name == 6){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "count" => $energy_amount, // 租用数量
                                    "period" => $rentTime, // 1h:1小时 1day:1天 3day:3天
                                    "trx_amount" => 0, // 选填，带宽手续费，type=both时有效, 比如 0.35
                                    "address" => $v->transferfrom_address // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://admin.weidubot.cc/api/trc_api/frozen_energy';
                                $dlres = Get_Pay($balance_url,$param);
                            //机器人开发代理
                            }elseif($v1->platform_name == 5){
                                $balance_url = env('THIRD_URL');
                                if(empty($balance_url)){
                                    $errorMessage = $errorMessage."使用开发者能量代理时,env中url为空";
                                    $save_data = [];
                                    $save_data['process_status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['process_comments'] = $time.$errorMessage;      //处理备注  
                                    EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                                
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                //使用开发者能量代理,仅支持65000 131000能量代理1小时
                                if($rentTime != 1){
                                    $errorMessage = $errorMessage."使用开发者能量代理时,时长只能为1小时";
                                    $save_data = [];
                                    $save_data['process_status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['process_comments'] = $time.$errorMessage;      //处理备注  
                                    EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                                
                                $param = [
                                    "tg_uid" => $v1->platform_uid, // 用户名,此处是tg的uid,因为是机器人能量代理模式
                                    "payNums" => $energy_amount, // 租用数量
                                    "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                                    "receiveAddress" => $v->transferfrom_address // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = $balance_url.'/api/thirdpart/shanzuorder';
                                $dlres = Get_Pay($balance_url,$param);
                            }
                            //NL-API平台（tgnl-home能量池系统）
                            elseif($v1->platform_name == 7){
                                // 获取tgnl-home域名，优先从环境变量，其次从comments字段
                                $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
                                if(empty($nlApiBaseUrl) && !empty($v1->comments)){
                                    // 尝试从comments中解析域名（格式：nl_api_url=https://xxx.com）
                                    if(preg_match('/nl_api_url=([^\s]+)/i', $v1->comments, $matches)){
                                        $nlApiBaseUrl = trim($matches[1]);
                                    }
                                }

                                if(empty($nlApiBaseUrl)){
                                    $errorMessage = $errorMessage."能量平台：".$v1->rid." NL-API域名未配置。";
                                    $save_data = [];
                                    $save_data['status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['comments'] = $time.$errorMessage;      //处理备注
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }

                                // platform_uid 作为 API username
                                $apiUsername = $v1->platform_uid;
                                // $signstr 已经是解密后的 platform_apikey，作为 API password
                                $apiPassword = $signstr;

                                if(empty($apiUsername) || empty($apiPassword)){
                                    $errorMessage = $errorMessage."能量平台：".$v1->rid." NL-API账户或密码未配置。";
                                    $save_data = [];
                                    $save_data['status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['comments'] = $time.$errorMessage;      //处理备注
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }

                                // 转换天数：0=1小时，1=1天，3=3天
                                if($energy_day == 1){
                                    $day = 1;
                                }elseif($energy_day == 3){
                                    $day = 3;
                                }else{
                                    $day = 0; // 默认1小时
                                }

                                $param = [
                                    'username' => $apiUsername,
                                    'password' => $apiPassword,
                                    'energy' => $energy_amount,
                                    'day' => $day,
                                    'receiver_address' => $v->wallet_addr
                                ];

                                $balance_url = rtrim($nlApiBaseUrl, '/') . '/v1/delegate_meal';
                                $header = [
                                    "Content-Type: application/json",
                                    "Accept: application/json"
                                ];
                                $dlres = Get_Pay($balance_url, json_encode($param), $header);
                            }
                            
                            if(empty($dlres)){
                                // $save_data = [];
                                // $save_data['process_status'] = 4;      //下单失败
                                // $save_data['process_comments'] = '下单失败,接口请求空';      //处理备注  
                                // $save_data['process_time'] = $time;      //处理时间
                                // $save_data['energy_platform_rid'] = $v1->rid;
                                // $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                // EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 能量平台接口返回为空。";
                                $save_data = [];
                                $save_data['process_status'] = 4;      //下单失败
                                $save_data['process_comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                $save_data['energy_platform_rid'] = $v1->rid;
                                $save_data['energy_package_rid'] = $res['rid'];
                                $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                continue;
                            }else{
                                $dlres = json_decode($dlres,true);
                                
                                if(
                                    (isset($dlres['status']) && $dlres['status'] == 200 && $v1->platform_name == 1) ||
                                    (isset($dlres['status']) && $dlres['status'] == 'success' && $v1->platform_name == 2) ||
                                    (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 3) ||
                                    (isset($dlres['code']) && $dlres['code'] == 10000 && $v1->platform_name == 4) ||
                                    (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 5) ||
                                    (isset($dlres['code']) && $dlres['code'] == 1 && $v1->platform_name == 6) ||
                                    ($v1->platform_name == 7 && ((isset($dlres['success']) && $dlres['success'] === true) || isset($dlres['tx_hash']) || isset($dlres['txHash'])))
                                ){
                                    if($v1->platform_name == 1){
                                        $orderNo = $dlres['data']['order_no'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 2){
                                        $orderNo = $dlres['txid'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 3){
                                        $orderNo = $dlres['data']['txid'];
                                        $use_trx = $dlres['data']['use_trx'];
                                    }elseif($v1->platform_name == 4){
                                        $orderNo = $dlres['data']['orderId'];
                                        $use_trx = $dlres['data']['orderMoney'];
                                    }elseif($v1->platform_name == 5){
                                        $orderNo = $dlres['data']['orderId'];
                                        $use_trx = $dlres['data']['orderMoney'];
                                    }elseif($v1->platform_name == 6){
                                        $orderNo = $dlres['data']['order_sn'];
                                        $use_trx = $dlres['data']['amount'];
                                    }elseif($v1->platform_name == 7){
                                        // NL-API 可能返回 tx_hash/txHash 和 cost_trx
                                        $orderNo = $dlres['tx_hash'] ?? $dlres['txHash'] ?? ('NLAPI_'.time());
                                        $use_trx = $dlres['cost_trx'] ?? 0;
                                    }
                                    $insert_data = [];
                                    $insert_data['energy_platform_rid'] = $v1->rid;
                                    $insert_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                    $insert_data['platform_name'] = $v1->platform_name;
                                    $insert_data['platform_uid'] = $v1->platform_uid;
                                    $insert_data['receive_address'] = $v->transferfrom_address;
                                    $insert_data['platform_order_id'] = $orderNo;
                                    $insert_data['energy_amount'] = $energy_amount;
                                    $insert_data['energy_day'] = $energy_day;	
                                    $insert_data['energy_time'] = $time;
                                    $insert_data['source_type'] = 2; //自动下单
                                    $insert_data['recovery_status'] = $v1->platform_name == 3 ?2:1; //回收状态:1不用回收,2待回收,3已回收	
                                    $insert_data['use_trx'] = $use_trx;
                                     
                                    $platform_order_rid = EnergyPlatformOrder::insertGetId($insert_data);
                                    $save_data = [];
                                    $save_data['process_status'] = 9;      //下单成功
                                    $save_data['process_comments'] = 'SUCCESS';      //处理备注  
                                    $save_data['platform_order_rid'] = $platform_order_rid;      //能量订单表ID	
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['energy_platform_rid'] = $v1->rid;
                                    $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                    $save_data['tg_notice_status_send'] = 'N';      //重新通知
                                    
                                    EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                    
                                    //如果是代理地址，扣代理trx余额
                                    if($isAgent == 'Y'){
                                        $agentUser->decrement('cash_trx',$res->agent_trx_price);
                                    }
                                    break; //跳出不轮询了
                                }else{
                                    if($v1->platform_name == 1){
                                        $msg = '下单失败,接口返回:'.$dlres['msg'];
                                    }elseif($v1->platform_name == 2){
                                        $msg = '下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 3){
                                        $msg = '下单失败,检查质押是否足够';
                                    }elseif($v1->platform_name == 4){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 5){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 6){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 7){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }
                                    $errorMessage = $errorMessage."能量平台：".$v1->platform_name.$msg;
                                    $save_data = [];
                                    $save_data['process_status'] = 4;      //下单失败
                                    $save_data['process_comments'] = $errorMessage;      //处理备注  
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['energy_platform_rid'] = $v1->rid;
                                    $save_data['energy_package_rid'] = $res['rid'];
                                    $save_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                    EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                            }
                        }
                        
                    }else{
                        $save_data = [];
                        $save_data['process_status'] = 4;      //下单失败
                        $save_data['process_comments'] = "机器人无可用能量平台,请质押或者充值平台";      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                    }
                }

            }else{
                // $this->log('shanduibonus','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('shanduibonus','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //能量快捷余额购买
        try {
            $data = EnergyQuickOrder::from('t_energy_quick_order as a')
                ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                ->where('a.status',1)
                ->where('a.energy_amount','>',0)
                ->select('a.rid','a.wallet_addr','a.energy_amount','a.energy_day','b.poll_group','b.status','b.bot_rid','b.rid as energy_platform_bot_rid','b.agent_tg_uid','a.package_rid')
                ->limit(100)
                ->get();
                    
            if($data->count() > 0){
                $time = nowDate();
                
                foreach ($data as $k => $v) {
                    if($v->status == 1){
                        $save_data = [];
                        $save_data['status'] = 6;  //能量钱包未启用
                        $save_data['comments'] = '能量钱包未启用';      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                        continue;
                    }
                    
                    $isAgent = 'N';
                    //如果是代理地址,查套餐的代理价格
                    if(!empty($v->agent_tg_uid)){
                        //匹配金额
                        $package = EnergyPlatformPackage::where('rid',$v->package_rid)->first();
                        if(empty($package)){
                            $save_data = [];
                            $save_data['status'] = 5;  //能量钱包未启用
                            $save_data['comments'] = '代理能量未找到该套餐';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                        if(empty($package->agent_trx_price) || $package->agent_trx_price <= 0){
                            $save_data = [];
                            $save_data['status'] = 5;  //能量钱包未启用
                            $save_data['comments'] = '套餐对应代理trx价格为0,无法扣款';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                        
                        $agentUser = TelegramBotUser::where('tg_uid',$v->agent_tg_uid)->where('bot_rid',$v->bot_rid)->first();
                        if(empty($agentUser)){
                            $save_data = [];
                            $save_data['status'] = 5;  //能量钱包未启用
                            $save_data['comments'] = '代理用户未关注该机器人,无法扣款';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            continue;
                        
                        }elseif($agentUser->cash_trx < $package->agent_trx_price){
                            $save_data = [];
                            $save_data['status'] = 5;  //能量钱包未启用
                            $save_data['comments'] = "代理地址对应用户TRX余额不足,无法扣款,需要：".($package->agent_trx_price + 0).",用户余额：".($agentUser->cash_trx + 0);      //处理备注
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            continue;
                        }elseif($agentUser->cash_trx >= $package->agent_trx_price){
                            $isAgent = 'Y';
                        }else{
                            $save_data = [];
                            $save_data['status'] = 5;  //能量钱包未启用
                            $save_data['comments'] = '代理校验未知错误';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                    }
                    
                    $energy_amount = $v->energy_amount;
                    //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
                    $model = EnergyPlatform::where('poll_group',$v->poll_group)
                            ->where('status',0)
                            ->whereNotNull('platform_apikey')
                            ->where(function ($query) use($energy_amount) {
                                $query->where(function ($query1) use($energy_amount){
                                     $query1->where('platform_name', 3)->where('platform_balance', '>=', $energy_amount);
                                });
                                $query->orwhere(function ($query2) {
                                     $query2->orwhereIn('platform_name', [1,2,4,5,6])->where('platform_balance', '>', '0');
                                 });
                             })
                            ->orderBy('seq_sn','desc')
                            ->get();
                    
                    if($model->count() > 0){
                        $errorMessage = '';
                        $rsa_services = new RsaServices();
                        
                        foreach ($model as $k1 => $v1){
                            $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                            
                            if(empty($signstr)){
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 平台私钥为空。";
                                $save_data = [];
                                $save_data['status'] = 5;      //下单失败
                                $save_data['comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                            
                            $save_data = [];
                            $save_data['status'] = 8;      //下单中
                            $save_data['comments'] = '下单中';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                            
                            $energy_day = $v->energy_day;
                            //neee.cc平台
                            if($v1->platform_name == 1){
                                $header = [
                                    "Content-Type:application/json"
                                ];
                                $param = [
                                    "uid" => strval($v1->platform_uid),
                                    "resource_type" => "0", //0能量
                                    "receive_address" => $v->wallet_addr,
                                    "amount" => strval($energy_amount),
                                    "freeze_day" => strval($energy_day), //0：一小时，1：一天，3：三天
                                    "time" => strval(time())
                                ];
                                
                        		ksort($param);
                        		reset($param);
                        
                        		foreach($param as $ka => $va){
                        			if($ka != "sign" && $ka != "sign_type" && $va!=''){
                        				$signstr .= $ka.$va;
                        			}
                        		}
                        		
                        		$sign = md5($signstr);
                        		$param['sign'] = $sign;
                                $balance_url = 'https://api.tronqq.com/openapi/v2/order/submit';
                                $dlres = Get_Pay($balance_url,json_encode($param),$header);
                            }
                            //RentEnergysBot平台
                            elseif($v1->platform_name == 2){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                $energy_amount = $energy_amount < 33000 ?33000:$energy_amount;
                    
                                $balance_url = 'https://api.wallet.buzz?api=getEnergy&apikey='.$signstr.'&address='.$v->wallet_addr.'&amount='.$energy_amount.'&type='.$type;
                                $dlres = Get_Pay($balance_url);
                            }
                            //自己质押代理
                            elseif($v1->platform_name == 3){
                                $params = [
                                    'pri' => $signstr,
                                    'fromaddress' => $v1->platform_uid,
                                    'receiveaddress' => $v->wallet_addr,
                                    'resourcename' => 'ENERGY',
                                    'resourceamount' => $energy_amount,
                                    'resourcetype' => 1,
                                    'permissionid' => $v1->permission_id
                                ];
                                $apiWebUrl = config('services.api_web.url');
                                $dlres = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete',$params);
                            //trongas.io平台
                            }elseif($v1->platform_name == 4){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "username" => $v1->platform_uid, // 用户名
                                    "password" => $signstr, // 用户密码
                                    "resType" => "ENERGY", // 资源类型，ENERGY：能量，BANDWIDTH：带宽
                                    "payNums" => $energy_amount, // 租用数量
                                    "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                                    "resLock" => 0, // 租用锁定，0：不锁定，1：锁定。能量租用数量不小于500万且租用时间不小于3天才能锁定。带宽租用数量不小于30万租用时间不小于3天才能锁定
                                    "receiveAddress" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://trongas.io/api/pay';
                                $dlres = Get_Pay($balance_url,$param);
                            //搜狐平台
                            }elseif($v1->platform_name == 6){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "count" => $energy_amount, // 租用数量
                                    "period" => $rentTime, // 1h:1小时 1day:1天 3day:3天
                                    "trx_amount" => 0, // 选填，带宽手续费，type=both时有效, 比如 0.35
                                    "address" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://admin.weidubot.cc/api/trc_api/frozen_energy';
                                $dlres = Get_Pay($balance_url,$param);
                            //机器人开发代理
                            }elseif($v1->platform_name == 5){
                                $balance_url = env('THIRD_URL');
                                if(empty($balance_url)){
                                    $errorMessage = $errorMessage."使用开发者能量代理时,env中url为空";
                                    $save_data = [];
                                    $save_data['status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                                
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                //使用开发者能量代理,仅支持65000 131000能量代理1小时
                                if($rentTime != 1){
                                    $errorMessage = $errorMessage."使用开发者能量代理时,时长只能为1小时";
                                    $save_data = [];
                                    $save_data['status'] = 4;      //下单失败
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                                
                                $param = [
                                    "tg_uid" => $v1->platform_uid, // 用户名,此处是tg的uid,因为是机器人能量代理模式
                                    "payNums" => $energy_amount, // 租用数量
                                    "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                                    "receiveAddress" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = $balance_url.'/api/thirdpart/shanzuorder';
                                $dlres = Get_Pay($balance_url,$param);
                            }
                            
                            if(empty($dlres)){
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 能量平台接口返回为空。";
                                $save_data = [];
                                $save_data['status'] = 4;      //下单失败
                                $save_data['comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                continue;
                            }else{
                                $dlres = json_decode($dlres,true);
                                
                                if((isset($dlres['status']) && $dlres['status'] == 200 && $v1->platform_name == 1) || (isset($dlres['status']) && $dlres['status'] == 'success' && $v1->platform_name == 2) || (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 3) || (isset($dlres['code']) && $dlres['code'] == 10000 && $v1->platform_name == 4) || (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 5) || (isset($dlres['code']) && $dlres['code'] == 1 && $v1->platform_name == 6)){
                                    if($v1->platform_name == 1){
                                        $orderNo = $dlres['data']['order_no'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 2){
                                        $orderNo = $dlres['txid'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 3){
                                        $orderNo = $dlres['data']['txid'];
                                        $use_trx = $dlres['data']['use_trx'];
                                    }elseif($v1->platform_name == 4){
                                        $orderNo = $dlres['data']['orderId'];
                                        $use_trx = $dlres['data']['orderMoney'];
                                    }elseif($v1->platform_name == 5){
                                        $orderNo = $dlres['data']['orderId'];
                                        $use_trx = $dlres['data']['orderMoney'];
                                    }elseif($v1->platform_name == 6){
                                        $orderNo = $dlres['data']['order_sn'];
                                        $use_trx = $dlres['data']['amount'];
                                    }
                                    $insert_data = [];
                                    $insert_data['energy_platform_rid'] = $v1->rid;
                                    $insert_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                    $insert_data['platform_name'] = $v1->platform_name;
                                    $insert_data['platform_uid'] = $v1->platform_uid;
                                    $insert_data['receive_address'] = $v->wallet_addr;
                                    $insert_data['platform_order_id'] = $orderNo;
                                    $insert_data['energy_amount'] = $energy_amount;
                                    $insert_data['energy_day'] = $energy_day;	
                                    $insert_data['energy_time'] = $time;
                                    $insert_data['source_type'] = 2; //自动下单
                                    $insert_data['recovery_status'] = $v1->platform_name == 3 ?2:1; //回收状态:1不用回收,2待回收,3已回收	
                                    $insert_data['use_trx'] = $use_trx;
                                     
                                    $platform_order_rid = EnergyPlatformOrder::insertGetId($insert_data);
                                    $save_data = [];
                                    $save_data['status'] = 9;      //下单成功
                                    $save_data['comments'] = 'SUCCESS';      //处理备注  
                                    $save_data['process_time'] = $time;      //处理时间
                                    $save_data['daili_time'] = $time;      //代理时间
                                    $save_data['is_notice'] = 'Y';      //重新通知
                                    
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    
                                    //如果是代理地址，扣代理trx余额
                                    if($isAgent == 'Y'){
                                        $agentUser->decrement('cash_trx',$package->agent_per_price);
                                    }
                                    break; //跳出不轮询了
                                }else{
                                    if($v1->platform_name == 1){
                                        $msg = '下单失败,接口返回:'.$dlres['msg'];
                                    }elseif($v1->platform_name == 2){
                                        $msg = '下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 3){
                                        $msg = '下单失败,检查质押是否足够';
                                    }elseif($v1->platform_name == 4){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 5){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 6){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }
                                    $errorMessage = $errorMessage."能量平台：".$v1->platform_name.$msg;
                                    $save_data = [];
                                    $save_data['status'] = 4;      //下单失败
                                    $save_data['comments'] = $errorMessage;      //处理备注  
                                    $save_data['process_time'] = $time;      //处理时间
                                    EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                            }
                        }
                        
                    }else{
                        $save_data = [];
                        $save_data['status'] = 4;      //下单失败
                        $save_data['comments'] = "机器人无可用能量平台,请质押或者充值平台";      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        EnergyQuickOrder::where('rid',$v->rid)->update($save_data);
                    }
                }

            }else{
                // $this->log('shanduibonus','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('shanduibonus','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //机器人开发代理-闪租下单
        try {
            $data = EnergyThirdPart::from('t_energy_third_part as a')
                ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                ->where('a.process_status',1)
                ->where('a.order_type',2)
                ->select('a.rid','a.wallet_addr','a.cishu_energy','b.poll_group','b.status','b.bot_rid','b.rid as energy_platform_bot_rid')
                ->limit(100)
                ->get();
                    
            if($data->count() > 0){
                $time = nowDate();
                
                foreach ($data as $k => $v) {
                    if($v->status == 1){
                        $save_data = [];
                        $save_data['process_status'] = 6;  //能量钱包未启用
                        $save_data['process_comments'] = '能量钱包未启用';      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                        continue;
                    }
                    
                    $energy_amount = $v->cishu_energy;
                    //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
                    $model = EnergyPlatform::where('poll_group',$v->poll_group)
                            ->where('status',0)
                            ->whereNotNull('platform_apikey')
                            ->where(function ($query) use($energy_amount) {
                                $query->where(function ($query1) use($energy_amount){
                                     $query1->where('platform_name', 3)->where('platform_balance', '>=', $energy_amount);
                                });
                                $query->orwhere(function ($query2) {
                                     $query2->orwhereIn('platform_name', [1,2,4,6])->where('platform_balance', '>', '0'); //这里不能有5这个平台
                                 });
                             })
                            ->orderBy('seq_sn','desc')
                            ->get();
                    
                    if($model->count() > 0){
                        $errorMessage = '';
                        $rsa_services = new RsaServices();
                        
                        foreach ($model as $k1 => $v1){
                            $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                            
                            if(empty($signstr)){
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 平台私钥为空。";
                                $save_data = [];
                                $save_data['process_status'] = 5;      //下单失败
                                $save_data['process_comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                            
                            $save_data = [];
                            $save_data['process_status'] = 8;      //下单中
                            $save_data['process_comments'] = '下单中';      //处理备注  
                            $save_data['process_time'] = $time;      //处理时间
                            EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                            
                            $energy_day = 0; //该方式下单只能是1小时
                            //neee.cc平台
                            if($v1->platform_name == 1){
                                $header = [
                                    "Content-Type:application/json"
                                ];
                                $param = [
                                    "uid" => strval($v1->platform_uid),
                                    "resource_type" => "0", //0能量
                                    "receive_address" => $v->wallet_addr,
                                    "amount" => strval($energy_amount),
                                    "freeze_day" => strval($energy_day), //0：一小时，1：一天，3：三天
                                    "time" => strval(time())
                                ];
                                
                        		ksort($param);
                        		reset($param);
                        
                        		foreach($param as $ka => $va){
                        			if($ka != "sign" && $ka != "sign_type" && $va!=''){
                        				$signstr .= $ka.$va;
                        			}
                        		}
                        		
                        		$sign = md5($signstr);
                        		$param['sign'] = $sign;
                                $balance_url = 'https://api.tronqq.com/openapi/v2/order/submit';
                                $dlres = Get_Pay($balance_url,json_encode($param),$header);
                            }
                            //RentEnergysBot平台
                            elseif($v1->platform_name == 2){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                $energy_amount = $energy_amount < 33000 ?33000:$energy_amount;
                    
                                $balance_url = 'https://api.wallet.buzz?api=getEnergy&apikey='.$signstr.'&address='.$v->wallet_addr.'&amount='.$energy_amount.'&type='.$type;
                                $dlres = Get_Pay($balance_url);
                            }
                            //自己质押代理
                            elseif($v1->platform_name == 3){
                                $params = [
                                    'pri' => $signstr,
                                    'fromaddress' => $v1->platform_uid,
                                    'receiveaddress' => $v->wallet_addr,
                                    'resourcename' => 'ENERGY',
                                    'resourceamount' => $energy_amount,
                                    'resourcetype' => 1,
                                    'permissionid' => $v1->permission_id
                                ];
                                $apiWebUrl = config('services.api_web.url');
                                $dlres = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete',$params);
                            //trongas.io平台
                            }elseif($v1->platform_name == 4){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "username" => $v1->platform_uid, // 用户名
                                    "password" => $signstr, // 用户密码
                                    "resType" => "ENERGY", // 资源类型，ENERGY：能量，BANDWIDTH：带宽
                                    "payNums" => $energy_amount, // 租用数量
                                    "rentTime" => $rentTime, // 单位小时，只能1时或1到30天按天租用其中不能租用2天
                                    "resLock" => 0, // 租用锁定，0：不锁定，1：锁定。能量租用数量不小于500万且租用时间不小于3天才能锁定。带宽租用数量不小于30万租用时间不小于3天才能锁定
                                    "receiveAddress" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://trongas.io/api/pay';
                                $dlres = Get_Pay($balance_url,$param);
                            //搜狐平台
                            }elseif($v1->platform_name == 6){
                                //0：一小时，1：一天，3：三天
                                switch ($energy_day) {
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
                                    "count" => $energy_amount, // 租用数量
                                    "period" => $rentTime, // 1h:1小时 1day:1天 3day:3天
                                    "trx_amount" => 0, // 选填，带宽手续费，type=both时有效, 比如 0.35
                                    "address" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                ];
                                
                                $balance_url = 'https://admin.weidubot.cc/api/trc_api/frozen_energy';
                                $dlres = Get_Pay($balance_url,$param);
                            }
                            
                            if(empty($dlres)){
                                $errorMessage = $errorMessage."能量平台：".$v1->platform_name." 能量平台接口返回为空。";
                                $save_data = [];
                                $save_data['process_status'] = 4;      //下单失败
                                $save_data['process_comments'] = $errorMessage;      //处理备注  
                                $save_data['process_time'] = $time;      //处理时间
                                EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                                continue;
                            }else{
                                $dlres = json_decode($dlres,true);
                                
                                if((isset($dlres['status']) && $dlres['status'] == 200 && $v1->platform_name == 1) || (isset($dlres['status']) && $dlres['status'] == 'success' && $v1->platform_name == 2) || (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 3) || (isset($dlres['code']) && $dlres['code'] == 10000 && $v1->platform_name == 4) || (isset($dlres['code']) && $dlres['code'] == 1 && $v1->platform_name == 6)){
                                    if($v1->platform_name == 1){
                                        $orderNo = $dlres['data']['order_no'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 2){
                                        $orderNo = $dlres['txid'];
                                        $use_trx = 0;
                                    }elseif($v1->platform_name == 3){
                                        $orderNo = $dlres['data']['txid'];
                                        $use_trx = $dlres['data']['use_trx'];
                                    }elseif($v1->platform_name == 4){
                                        $orderNo = $dlres['data']['orderId'];
                                        $use_trx = $dlres['data']['orderMoney'];
                                    }elseif($v1->platform_name == 6){
                                        $orderNo = $dlres['data']['order_sn'];
                                        $use_trx = $dlres['data']['amount'];
                                    }
                                    $insert_data = [];
                                    $insert_data['energy_platform_rid'] = $v1->rid;
                                    $insert_data['energy_platform_bot_rid'] = $v->energy_platform_bot_rid;
                                    $insert_data['platform_name'] = $v1->platform_name;
                                    $insert_data['platform_uid'] = $v1->platform_uid;
                                    $insert_data['receive_address'] = $v->wallet_addr;
                                    $insert_data['platform_order_id'] = $orderNo;
                                    $insert_data['energy_amount'] = $energy_amount;
                                    $insert_data['energy_day'] = $energy_day;	
                                    $insert_data['energy_time'] = $time;
                                    $insert_data['source_type'] = 2; //自动下单
                                    $insert_data['recovery_status'] = $v1->platform_name == 3 ?2:1; //回收状态:1不用回收,2待回收,3已回收	
                                    $insert_data['use_trx'] = $use_trx;
                                     
                                    $platform_order_rid = EnergyPlatformOrder::insertGetId($insert_data);
                                    $save_data = [];
                                    $save_data['process_status'] = 9;      //下单成功
                                    $save_data['process_comments'] = 'SUCCESS';      //处理备注  
                                    $save_data['process_time'] = $time;      //处理时间
                                    // $save_data['tg_notice_status_send'] = 'N';      //重新通知
                                    
                                    EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                                    break; //跳出不轮询了
                                }else{
                                    if($v1->platform_name == 1){
                                        $msg = '下单失败,接口返回:'.$dlres['msg'];
                                    }elseif($v1->platform_name == 2){
                                        $msg = '下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 3){
                                        $msg = '下单失败,检查质押是否足够';
                                    }elseif($v1->platform_name == 4){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 6){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }
                                    $errorMessage = $errorMessage."能量平台：".$v1->platform_name.$msg;
                                    $save_data = [];
                                    $save_data['process_status'] = 4;      //下单失败
                                    $save_data['process_comments'] = $errorMessage;      //处理备注  
                                    $save_data['process_time'] = $time;      //处理时间
                                    EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                            }
                        }
                        
                    }else{
                        $save_data = [];
                        $save_data['process_status'] = 4;      //下单失败
                        $save_data['process_comments'] = "机器人无可用能量平台,请质押或者充值平台";      //处理备注  
                        $save_data['process_time'] = $time;      //处理时间
                        EnergyThirdPart::where('rid',$v->rid)->update($save_data);
                    }
                }

            }else{
                // $this->log('shanduibonus','----------没有数据----------');
            }
        }catch (\Exception $e){
            // $this->log('shanduibonus','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //usdt笔数套餐
        try {
            $data = EnergyWalletTradeList::from('t_energy_wallet_trade_list as a')
                ->join('t_energy_platform_bot as b','a.transferto_address','b.receive_wallet')
                ->leftJoin('t_telegram_bot as c','b.bot_rid','c.rid')
                ->where('a.process_status',1)
                ->where('a.coin_name','usdt')
                ->select('a.rid','a.transferfrom_address','a.amount','b.bot_rid','b.per_bishu_usdt_price','b.tg_notice_obj_send','c.bot_token','c.bot_username','c.bot_admin_username','b.bishu_stop_day')
                ->limit(100)
                ->get();
                    
            if($data->count() > 0){
                $time = nowDate();
                
                foreach ($data as $k => $v) {
                    //查询笔数套餐钱包是否存在
                    $energyAiBishu = EnergyAiBishu::where('wallet_addr',$v->transferfrom_address)->first();
                    if($energyAiBishu){
                        $save_data = [];
                        $save_data['is_buy'] = ($energyAiBishu->total_buy_quantity == $energyAiBishu->max_buy_quantity) ? 'Y' : ($energyAiBishu->status == 1 ?'Y':$energyAiBishu->is_buy);
                        $save_data['total_buy_usdt'] = $energyAiBishu->total_buy_usdt + $v->amount;
                        $save_data['max_buy_quantity'] = $energyAiBishu->max_buy_quantity + floor($v->amount / $v->per_bishu_usdt_price);
                        $save_data['status'] = $energyAiBishu->status == 1 ?0:$energyAiBishu->status;
                        $save_data['bishu_stop_day'] = ($v->bishu_stop_day > 0 && $energyAiBishu->bishu_stop_day < $v->bishu_stop_day) ?$v->bishu_stop_day:$energyAiBishu->bishu_stop_day;
                        EnergyAiBishu::where('rid',$energyAiBishu->rid)->update($save_data);
                        
                    }else{
                        $insert_data = [];
                        $insert_data['bot_rid'] = $v->bot_rid;
                        $insert_data['wallet_addr'] = $v->transferfrom_address;
                        $insert_data['status'] = 0;
                        $insert_data['total_buy_usdt'] = $v->amount;
                        $insert_data['max_buy_quantity'] = floor($v->amount / $v->per_bishu_usdt_price);
                        $insert_data['create_time'] = $time;
                        $insert_data['is_buy'] = 'Y';
                        $insert_data['bishu_stop_day'] = $v->bishu_stop_day;
                        EnergyAiBishu::insert($insert_data);
                    }
                    
                    $save_data = [];
                    $save_data['process_status'] = 9;      //下单成功
                    $save_data['process_comments'] = "成功,笔数套餐增加：".floor($v->amount / $v->per_bishu_usdt_price);      //处理备注  
                    $save_data['process_time'] = $time;      //处理时间
                    EnergyWalletTradeList::where('rid',$v->rid)->update($save_data);
                    
                    //通知到群
                    if(!empty($v->tg_notice_obj_send) && $v->tg_notice_obj_send != ''){
                        $replytext = "<b>✳️笔数套餐购买成功</b> \n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."<b>下单模式</b>：笔数套餐\n"
                            ."<b>免费转账</b>：". floor($v->amount / $v->per_bishu_usdt_price) ." 次\n"
                            ."<b>下单地址</b>：".mb_substr($v->transferfrom_address,0,8).'****'.mb_substr($v->transferfrom_address,-8,8) ."\n\n"
                            ."<b>笔数套餐转账不扣TRX，智能监控地址补足能量</b>\n"
                            ."发送 /buyenergy 继续购买能量！\n"
                            ."➖➖➖➖➖➖➖➖";
                        
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '能量闪租', 'url' => 'https://t.me/'.$v->bot_username],
                                    ['text' => '笔数套餐', 'url' => 'https://t.me/'.$v->bot_username]
                                ],
                                [
                                    ['text' => '联系客服', 'url' => 'https://t.me/'.mb_substr($v->bot_admin_username,1)],
                                    ['text' => 'TRX闪兑', 'url' => 'https://t.me/'.$v->bot_username]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                        
                        $sendlist = explode(',',$v->tg_notice_obj_send);
                    
                        foreach ($sendlist as $x => $y) {
                            $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML&reply_markup='.urlencode($encodedKeyboard);
                            Get_Pay($sendmessageurl);
                        }
                        
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

}