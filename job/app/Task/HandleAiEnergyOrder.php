<?php
namespace App\Task;

use App\Model\Energy\EnergyPlatformOrder;
use App\Model\Energy\EnergyAiTrusteeship;
use App\Model\Energy\EnergyAiBishu;
use App\Model\Energy\EnergyPlatform;
use App\Model\Telegram\TelegramBotUser;
use App\Service\RsaServices;
use App\Library\Log;

class HandleAiEnergyOrder
{
    public function execute()
    { 
        //智能托管
        try {
            $data = EnergyAiTrusteeship::from('t_energy_ai_trusteeship as a')
                ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                ->where('a.is_buy','Y')
                ->where('a.status',0)
                ->where('b.is_open_ai_trusteeship','Y')
                ->where('b.status',0)
                ->where('a.per_buy_energy_quantity','>=',65000)
                ->where('b.trx_price_energy_32000','>',0)
                ->where('b.trx_price_energy_65000','>',0)
                ->whereIn('b.per_energy_day',[0,1,3])
                ->select('a.rid','a.wallet_addr','a.tg_uid','a.per_buy_energy_quantity','b.trx_price_energy_32000','b.trx_price_energy_65000','b.per_energy_day','b.status','a.is_notice','a.bot_rid','a.total_buy_energy_quantity','a.total_used_trx','a.total_buy_quantity','a.is_notice_admin','b.poll_group','b.rid as energy_platform_bot_rid','b.agent_tg_uid','b.agent_per_price')
                ->limit(100)
                ->get();
                
            if($data->count() > 0){
                $time = nowDate();
                foreach ($data as $k => $v) {
                    $isAgent = 'N';
                    //查是否是代理，是代理则判断余额，需要扣除余额
                    if(!empty($v->agent_tg_uid)){
                        if(empty($v->agent_per_price) || $v->agent_per_price <= 0){
                            $errorMessage = "代理地址对应用户未设置每笔金额,无法扣款";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                        
                        $agentUser = TelegramBotUser::where('tg_uid',$v->agent_tg_uid)->where('bot_rid',$v->bot_rid)->first();
                        if(empty($agentUser)){
                            $errorMessage = "代理用户未关注该机器人,无法扣款";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                            continue;
                        
                        }elseif($agentUser->cash_trx < $v->agent_per_price){
                            $errorMessage = "代理地址对应用户TRX余额不足,无法扣款,需要：".($v->agent_per_price + 0).",用户余额：".($agentUser->cash_trx + 0);
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                            continue;
                        }elseif($agentUser->cash_trx >= $v->agent_per_price){
                            $isAgent = 'Y';
                        }else{
                            $errorMessage = "代理校验未知错误";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                    }
                    
                    //判断是否在代理之前回收之前还未回收的能量
                    if(isset($v->ai_trusteeship_recovery_type) && $v->ai_trusteeship_recovery_type == 2){
                        //查平台信息
                        $recoveryPlatform = EnergyPlatform::where('poll_group',$v->poll_group)->where('status',0)->whereNotNull('platform_apikey')->where('platform_name',3)->get();
                        
                        if($recoveryPlatform->count() > 0){
                            foreach ($recoveryPlatform as $a => $b){
                                //查询质押地址是否还有对该地址有未回收的能量
                                $recoveryOrder = EnergyPlatformOrder::where('platform_uid', $b->platform_uid)->where('energy_platform_bot_rid', $v->energy_platform_bot_rid)->where('receive_address' ,$v->wallet_addr)->where('recovery_status', 2)->where('source_type',3)->sum('use_trx');
                                
                                if(!empty($recoveryOrder) && $recoveryOrder > 0){
                                    $rsa_services = new RsaServices();
                                    $platform_recoveryapikey = $rsa_services->privateDecrypt($b->platform_apikey);
                                    if(!empty($platform_recoveryapikey)){
                                        //调用接口回收
                                        $params = [
                                            'pri' => $platform_recoveryapikey,
                                            'fromaddress' => $b->platform_uid,
                                            'receiveaddress' => $v->wallet_addr,
                                            'resourcename' => 'ENERGY',
                                            'resourceamount' => (int)$recoveryOrder,
                                            'resourcetype' => 3, //资源方式：1代理资源,2回收资源(按能量),3回收资源(按TRX)
                                            'permissionid' => $b->permission_id
                                        ];
                                        $apiWebUrl = config('services.api_web.url');
                                        $recoveryRes = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete',$params);
                                        
                                        //如果成功,更新数据
                                        if(!empty($recoveryRes)){
                                            $recoveryRes = json_decode($recoveryRes,true);
                                            if(isset($recoveryRes['code']) && $recoveryRes['code'] == 200){
                                                EnergyPlatformOrder::where('platform_uid', $b->platform_uid)->where('energy_platform_bot_rid', $v->energy_platform_bot_rid)->where('receive_address' ,$v->wallet_addr)->where('recovery_status', 2)->where('source_type',3)
                                                                    ->update(["recovery_status" => 3,"recovery_time" => $time]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                        
                    //如果超过了次数则不处理（t_energy_ai_trusteeship 表没有 max_buy_quantity 字段，跳过此检查）
                    // if($v->max_buy_quantity > 0 && $v->max_buy_quantity <= $v->total_buy_quantity){
                    //     continue;
                    // }
                    $energy_amount = $v->per_buy_energy_quantity;
                    
                    //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
                    $model = EnergyPlatform::where('poll_group',$v->poll_group)
                            ->where('status',0)
                            ->whereNotNull('platform_apikey')
                            ->where(function ($query) use($energy_amount) {
                                $query->where(function ($query1) use($energy_amount){
                                     $query1->where('platform_name', 3)->where('platform_balance', '>=', $energy_amount);
                                });
                                $query->orwhere(function ($query2) {
                                     $query2->orwhereIn('platform_name', [1,2,4])->where('platform_balance', '>', '0');
                                 });
                             })
                            ->orderBy('seq_sn','desc')
                            ->get();
                    
                    if($model->count() > 0){
                        $errorMessage = '';
                        $rsa_services = new RsaServices();
                        $lunxunCount = 0;
                        
                        foreach ($model as $k1 => $v1){
                            $lunxunCount = $lunxunCount + 1;
                            $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                            
                            if(empty($signstr)){
                                $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 平台私钥为空。";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                            
                            //判断用户金额是否足够,金额不足的时候,直接跳出,不轮询了
                            $botuser = TelegramBotUser::where('bot_rid',$v->bot_rid)->where('tg_uid',$v->tg_uid)->first();
                            if(empty($botuser)){
                                $errorMessage = $errorMessage."找不到机器人用户数据。";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                break;
                            }
                            
                            $kou_price = $v->per_buy_energy_quantity == 65000 ?$v->trx_price_energy_32000:$v->trx_price_energy_65000;
                            
                            if($botuser->cash_trx < $kou_price){
                                $errorMessage = $errorMessage.'余额不足,需要：'.$kou_price;
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                break;
                            }
                            
                            $save_data = [];
                            $save_data['is_buy'] = 'B';      //下单中
                            EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                            
                            $energy_day = $v->per_energy_day;
                            
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
                            }
                            
                            if(empty($dlres)){
                                $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 下单失败,接口请求空。";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;
                                $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                continue;
                            }else{
                                $dlres = json_decode($dlres,true);
                                if((isset($dlres['status']) && $dlres['status'] == 200 && $v1->platform_name == 1) || (isset($dlres['status']) && $dlres['status'] == 'success' && $v1->platform_name == 2) || (isset($dlres['code']) && $dlres['code'] == 200 && $v1->platform_name == 3) || (isset($dlres['code']) && $dlres['code'] == 10000 && $v1->platform_name == 4)){
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
                                    $insert_data['source_type'] = 3; //智能托管
                                    $insert_data['recovery_status'] = $v1->platform_name == 3 ?2:1; //回收状态:1不用回收,2待回收,3已回收	
                                    $insert_data['use_trx'] = $use_trx;
                                    $platform_order_rid = EnergyPlatformOrder::insertGetId($insert_data);
                                    
                                    $save_data = [];
                                    $save_data['is_buy'] = 'N';      //下单成功
                                    $save_data['comments'] = 'SUCCESS '.$time;      //处理备注  
                                    $save_data['is_notice'] = $v->is_notice == 'N' ?'Y':$v->is_notice;
                                    $save_data['total_buy_energy_quantity'] = $v->total_buy_energy_quantity + $energy_amount;
                                    $save_data['total_used_trx'] = $v->total_used_trx + $kou_price;
                                    $save_data['total_buy_quantity'] = $v->total_buy_quantity + 1;
                                    $save_data['last_buy_time'] = $time;
                                    $save_data['last_used_trx'] = $kou_price;
                                    EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                    
                                    $save_data = [];
                                    $save_data['cash_trx'] = $botuser->cash_trx - $kou_price;
                                    TelegramBotUser::where('rid',$botuser->rid)->update($save_data);
                                    
                                    //如果是代理地址，扣代理trx余额
                                    if($isAgent == 'Y'){
                                        $agentUser->decrement('cash_trx',$v->agent_per_price);
                                    }
                                    
                                    break; //跳出不轮询了
                                }else{
                                    if($v1->platform_name == 1){
                                        $msg = ' 下单失败,接口返回:'.$dlres['msg'];
                                    }elseif($v1->platform_name == 2){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }elseif($v1->platform_name == 3){
                                        $msg = ' 下单失败,检查质押是否足够';
                                    }elseif($v1->platform_name == 4){
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                    }
                                    $errorMessage = $errorMessage."能量平台ID：".$v1->rid.$msg;
                                    $save_data = [];
                                    $save_data['comments'] = $time.$errorMessage;
                                    $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                    EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                            }
                        }
                    }else{
                        $save_data = [];
                        $save_data['comments'] = $time.' 无可用能量平台,轮询失败,请质押或者充值平台';      //处理备注  
                        EnergyAiTrusteeship::where('rid',$v->rid)->update($save_data);
                    }
                }

            }else{
                // $this->log('handleaienergyorder','----------没有数据----------');
            }
        }catch (\Exception $e){
            $this->log('handleaienergyorder','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
        
        //笔数套餐
        try {
            $data = EnergyAiBishu::from('t_energy_ai_bishu as a')
                ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                ->where('a.is_buy','Y')
                ->where('a.status',0)
                ->where('a.max_buy_quantity','>',0)
                ->where('a.current_energy_quantity','<',65000)
                ->where('b.is_open_bishu','Y')
                ->where('b.status',0)
                // 注意：t_energy_platform_bot 表没有 per_bishu_energy_quantity 字段，先注释掉
                // ->where('b.per_bishu_energy_quantity','>=',65000)
                ->whereColumn('a.max_buy_quantity','>','a.total_buy_quantity')
                ->select('a.rid','a.wallet_addr','a.tg_uid','b.per_energy_day_bishu','b.status','a.is_notice','a.bot_rid','a.total_buy_energy_quantity','a.total_buy_quantity','a.is_notice_admin','b.poll_group','b.rid as energy_platform_bot_rid','a.max_buy_quantity','b.bishu_recovery_type','b.bishu_daili_type','b.agent_tg_uid','b.agent_per_price')
                ->limit(100)
                ->get();
                
            if($data->count() > 0){
                
                $time = nowDate();
                foreach ($data as $k => $v) {
                    $isAgent = 'N';
                    //查是否是代理，是代理则判断余额，需要扣除余额
                    if(!empty($v->agent_tg_uid)){
                        if(empty($v->agent_per_price) || $v->agent_per_price <= 0){
                            $errorMessage = "代理地址对应用户未设置每笔金额,无法扣款";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                        
                        $agentUser = TelegramBotUser::where('tg_uid',$v->agent_tg_uid)->where('bot_rid',$v->bot_rid)->first();
                        if(empty($agentUser)){
                            $errorMessage = "代理用户未关注该机器人,无法扣款";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            continue;
                        
                        }elseif($agentUser->cash_trx < $v->agent_per_price){
                            $errorMessage = "代理地址对应用户TRX余额不足,无法扣款,需要：".($v->agent_per_price + 0).",用户余额：".($agentUser->cash_trx + 0);
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            continue;
                        }elseif($agentUser->cash_trx >= $v->agent_per_price){
                            $isAgent = 'Y';
                        }else{
                            $errorMessage = "代理校验未知错误";
                            $save_data = [];
                            $save_data['comments'] = $time.$errorMessage;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            continue;
                        }
                    }
                    
                    if($v->bishu_daili_type == 1){
                        //判断是否在代理之前回收之前还未回收的能量
                        if($v->bishu_recovery_type == 2){
                            //查平台信息
                            $recoveryPlatform = EnergyPlatform::where('poll_group',$v->poll_group)->where('status',0)->whereNotNull('platform_apikey')->where('platform_name',3)->get();
                            
                            if($recoveryPlatform->count() > 0){
                                foreach ($recoveryPlatform as $a => $b){
                                    //查询质押地址是否还有对该地址有未回收的能量
                                    $recoveryOrder = EnergyPlatformOrder::where('platform_uid', $b->platform_uid)->where('energy_platform_bot_rid', $v->energy_platform_bot_rid)->where('receive_address' ,$v->wallet_addr)->where('recovery_status', 2)->where('source_type',4)->sum('use_trx');
                                    
                                    if(!empty($recoveryOrder) && $recoveryOrder > 0){
                                        $rsa_services = new RsaServices();
                                        $platform_recoveryapikey = $rsa_services->privateDecrypt($b->platform_apikey);
                                        // ---------- 新增重试逻辑 ----------
                                        $maxRetries = 3; // 最大重试次数
                                        $isSuccess = false;
                                        
                                        $params = [
                                            'pri' => $platform_recoveryapikey,
                                            'fromaddress' => $b->platform_uid,
                                            'receiveaddress' => $v->wallet_addr,
                                            'resourcename' => 'ENERGY',
                                            'resourceamount' => (int)$recoveryOrder,
                                            'resourcetype' => 3, //资源方式：1代理资源,2回收资源(按能量),3回收资源(按TRX)
                                            'permissionid' => $b->permission_id
                                        ];
                            
                                        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
                                            // 调用API
                                            $apiWebUrl = config('services.api_web.url');
                                            $recoveryRes = Get_Pay($apiWebUrl . '/api/tron/delegaandundelete', $params);
                                            
                                            // 解析响应
                                            if (!empty($recoveryRes)) {
                                                $recoveryRes = json_decode($recoveryRes, true);
                                                if (isset($recoveryRes['code']) && $recoveryRes['code'] == 200) {
                                                    $isSuccess = true;
                                                    break; // 成功则跳出重试循环
                                                }
                                            }
                                            
                                            // 非最后一次尝试时等待1秒（可选）
                                            if ($attempt < $maxRetries) {
                                                sleep(1);
                                            }
                                        }
                                        // ---------- 重试逻辑结束 ----------
                            
                                        // 仅当成功时更新数据
                                        if ($isSuccess) {
                                            EnergyPlatformOrder::where('platform_uid', $b->platform_uid)
                                                ->where('energy_platform_bot_rid', $v->energy_platform_bot_rid)
                                                ->where('receive_address', $v->wallet_addr)
                                                ->where('recovery_status', 2)
                                                ->where('source_type', 4)
                                                ->update([
                                                    "recovery_status" => 3,
                                                    "recovery_time" => $time
                                                ]);
                                        } else {
                                            $this->log('energyplatformbalance',$v->wallet_addr.'，笔数给能量时，回收失败，重试：'.$maxRetries.'次都失败，检查');
                                        }
                                    }
                                }
                            }
                        }
                        
                        //如果超过了次数则不处理
                        if($v->max_buy_quantity > 0 && $v->max_buy_quantity <= $v->total_buy_quantity){
                            continue;
                        }
                        $energy_amount = $v->per_bishu_energy_quantity;
                        
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
                                // ->orderBy('seq_sn','desc')
                                ->inRandomOrder() // 随机排序
                                ->get();
                        
                        if($model->count() > 0){
                            $errorMessage = '';
                            $rsa_services = new RsaServices();
                            $lunxunCount = 0;
                            
                            foreach ($model as $k1 => $v1){
                                $lunxunCount = $lunxunCount + 1;
                                $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                                
                                if(empty($signstr)){
                                    $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 平台私钥为空。";
                                    $save_data = [];
                                    $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                    EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                    continue;
                                }
                                
                                $save_data = [];
                                $save_data['is_buy'] = 'B';      //下单中
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                
                                //neee.cc平台
                                if($v1->platform_name == 1){
                                    $energy_day = $v->per_energy_day_bishu >= 30 ?1:$v->per_energy_day_bishu; //该平台因为不能手工回收,所以如果选择了30天,默认只代理一天
                                    
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
                                    $energy_day = $v->per_energy_day_bishu >= 30 ?1:$v->per_energy_day_bishu; //该平台因为不能手工回收,所以如果选择了30天,默认只代理一天
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
                                    $energy_day = $v->per_energy_day_bishu; //自己质押的可以是30天
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
                                    $energy_day = $v->per_energy_day_bishu >= 30 ?1:$v->per_energy_day_bishu; //该平台因为不能手工回收,所以如果选择了30天,默认只代理一天
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
                                    $energy_day = $v->per_energy_day_bishu >= 30 ?1:$v->per_energy_day_bishu; //该平台因为不能手工回收,所以如果选择了30天,默认只代理一天
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
                                        "type" => "both", // 资源类型，energy:能量  both:能量+带宽
                                        "count" => $energy_amount, // 租用数量
                                        "period" => $rentTime, // 1h:1小时 1day:1天 3day:3天
                                        "trx_amount" => 0.35, // 选填，带宽手续费，type=both时有效, 比如 0.35
                                        "address" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                    ];
                                    
                                    $balance_url = 'https://admin.weidubot.cc/api/trc_api/frozen_energy';
                                    $dlres = Get_Pay($balance_url,$param);
                                //机器人开发代理
                                }elseif($v1->platform_name == 5){
                                    $energy_day = 1;
                                    $balance_url = env('THIRD_URL');
                                    if(empty($balance_url)){
                                        $errorMessage = $errorMessage."使用开发者能量代理时,env中url为空";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }
                                    
                                    $param = [
                                        "tg_uid" => $v1->platform_uid, // 用户名,此处是tg的uid,因为是机器人能量代理模式
                                        "maxDelegateNums" => $v->max_buy_quantity - $v->total_buy_quantity, // 最大委托笔数，当为购买笔数时作为购买笔数的数量
                                        "receiveAddress" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                    ];
                                    
                                    $balance_url = $balance_url.'/api/thirdpart/bishuorder';
                                    $dlres = Get_Pay($balance_url,$param);
                                }
                                
                                if(empty($dlres)){
                                    $failCount = getRedis('bishuenergyfail'.$v->rid) ?? 0;
                                    if(empty($failCount) || $failCount < 3){
                                        $cacheCount = $failCount + 1;
                                        setexRedis('bishuenergyfail'.$v->rid,120,$cacheCount);
                                        EnergyAiBishu::where('rid', $v->rid)->update(['is_buy' => 'Y','comments' => '返回错误，重试'.$cacheCount.'次:'.$time.$msg]);
                                        
                                    }else{
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 下单失败,接口请求空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                    }
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
                                        }elseif($v->platform_name == 6){
                                            $orderNo = $dlres['data']['order_sn'];
                                            $use_trx = $dlres['data']['amount'];
                                        }
                                        if($v1->platform_name != 5){
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
                                            $insert_data['source_type'] = 4;
                                            $insert_data['recovery_status'] = $v1->platform_name == 3 ?2:1; //回收状态:1不用回收,2待回收,3已回收	
                                            $insert_data['use_trx'] = $use_trx;
                                            $platform_order_rid = EnergyPlatformOrder::insertGetId($insert_data);
                                        }
                                        
                                        $save_data = [];
                                        $save_data['is_buy'] = 'N';      //下单成功
                                        $save_data['comments'] = 'SUCCESS '.$time;      //处理备注  
                                        $save_data['is_notice'] = $v->is_notice == 'N' ?'Y':$v->is_notice;
                                        $save_data['total_buy_energy_quantity'] = $v->total_buy_energy_quantity + $energy_amount;
                                        $save_data['total_buy_quantity'] = $v->total_buy_quantity + ($v1->platform_name == 5 ?($v->max_buy_quantity - $v->total_buy_quantity):1);
                                        $save_data['last_buy_time'] = $time;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        
                                        //如果是代理地址，扣代理trx余额
                                        if($isAgent == 'Y'){
                                            $agentUser->decrement('cash_trx',$v->agent_per_price);
                                        }
                                        
                                        break; //跳出不轮询了
                                    }else{
                                        if($v1->platform_name == 1){
                                            $msg = ' 下单失败,接口返回:'.$dlres['msg'];
                                        }elseif($v1->platform_name == 2){
                                            $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                        }elseif($v1->platform_name == 3){
                                            $msg = ' 下单失败,检查质押是否足够';
                                        }elseif($v1->platform_name == 4){
                                            $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                        }elseif($v1->platform_name == 5){
                                            $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                        }elseif($v1->platform_name == 6){
                                            $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                        }
                                        
                                        $failCount = getRedis('bishuenergyfail'.$v->rid) ?? 0;
                                        if(empty($failCount) || $failCount < 3){
                                            $cacheCount = $failCount + 1;
                                            setexRedis('bishuenergyfail'.$v->rid,120,$cacheCount);
                                            EnergyAiBishu::where('rid', $v->rid)->update(['is_buy' => 'Y','comments' => '返回错误，重试'.$cacheCount.'次:'.$time.$msg]);
                                            
                                        }else{
                                            $errorMessage = $errorMessage."能量平台ID：".$v1->rid.$msg;
                                            $save_data = [];
                                            $save_data['comments'] = $time.$errorMessage;
                                            $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        }
                                        continue;
                                    }
                                }
                            }
                        }else{
                            $save_data = [];
                            $save_data['comments'] = $time.' 无可用能量平台,轮询失败,请质押或者充值平台';      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                        }
                        
                    //提交给第三方处理,trongas.io平台
                    }elseif($v->bishu_daili_type == 2){
                        $time = nowDate();
                        $energy_bishu = $v->max_buy_quantity - $v->total_buy_quantity;
                        
                        if($isAgent == 'Y'){
                            if($agentUser->cash_trx < $energy_bishu * $v->agent_per_price){
                                $errorMessage = "代理地址对应用户TRX余额不足,无法扣款,需要：".($energy_bishu * $v->agent_per_price + 0).",用户余额：".($agentUser->cash_trx + 0);
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }elseif($agentUser->cash_trx >= $energy_bishu * $v->agent_per_price){
                                $isAgent = 'Y';
                            }else{
                                $errorMessage = "代理校验未知错误";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                        }
                        
                        if($energy_bishu > 0){
                            //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
                            $bishuModel = EnergyPlatform::where('poll_group',$v->poll_group)
                                    ->where('status',0)
                                    ->whereNotNull('platform_apikey')
                                    ->where('platform_name',4)
                                    ->where('platform_balance','>',0)
                                    ->orderBy('seq_sn','desc')
                                    ->get();
                            if($bishuModel->count() > 0){
                                $errorMessage = '';
                                $rsa_services = new RsaServices();
                                $lunxunCount = 0;
                                
                                foreach ($bishuModel as $k1 => $v1){
                                    $lunxunCount = $lunxunCount + 1;
                                    $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                                    
                                    if(empty($signstr)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 平台私钥为空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }
                                    
                                    $payment = env('TRONGASID_BISHU_PAYMENT') ?? 0;
                                    $this->log('handleaienergyorder','提交平台'.$v1->platform_uid.'。提交地址：'.$v->wallet_addr.'。提交次数'.$energy_bishu.'。此时最大次数：'.$v->max_buy_quantity.'。此时已够次数'.$v->total_buy_quantity.'。提交方式：'.$payment);
                                    
                                    $param = [
                                        "username" => $v1->platform_uid, // 用户名
                                        "password" => $signstr, // 用户密码
                                        "resType" => "ENERGY", // 资源类型，ENERGY：能量
                                        "autoType" => 0, // 智能托管类型：0（笔数），1：（智能）。智能模式暂停
                                        "payment" => intval($payment), // 当为购买笔数时填写 1 （提交时,就扣除余额），其他场景填写 0（代理的时候才扣一次）
                                        "autoLimitNums" => 65000, // 少于指定的数量，将触发委托. 笔数模式填写65000
                                        "everyAutoNums" => 65000, // 触发委托的代理数量。笔数模式填写65000，智能模式不填将根据差量委托数量
                                        "endTime" => 2556115199, // 未来时间的秒时间戳，当为购买笔数时填写1735660799
                                        "rentTime" => 24, // 委托租用时间。智能模式有效，笔数模式填写24
                                        "maxDelegateNums" => $energy_bishu, // 最大委托笔数，当为购买笔数时作为购买笔数的数量
                                        "chromeIndex" => thirteenTime(), // 搜订单归集标识，用于搜索。如16953571121115046
                                        "receiveAddress" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                    ];
                                    
                                    $balance_url = 'https://trongas.io/api/auto/add';
                                    $dlres = post_url($balance_url,$param);
                                    
                                    if(empty($dlres)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 下单失败,接口请求空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }else{
                                        $this->log('handleaienergyorder','提交地址：'.$v->wallet_addr.'。平台返回：'.json_encode($dlres));
                                        
                                        if((isset($dlres['code']) && $dlres['code'] == 10000 && $v1->platform_name == 4)){
                                            if($v1->platform_name == 4){
                                                $orderNo = $dlres['data']['orderId'];
                                                $use_trx = $dlres['data']['orderMoney'];
                                            }
                                            
                                            $save_data = [];
                                            $save_data['is_buy'] = 'N';      //下单成功
                                            $save_data['comments'] = 'SUCCESS '.$time.' 第三方平台下单,本次次数：'.$energy_bishu;      //处理备注  
                                            $save_data['is_notice'] = 'N';
                                            $save_data['total_buy_quantity'] = $v->max_buy_quantity;
                                            $save_data['last_buy_time'] = $time;
                                            $save_data['energy_platform_rid'] = $v1->rid;
                                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                            
                                            //如果是代理地址，扣代理trx余额
                                            if($isAgent == 'Y'){
                                                $agentUser->decrement('cash_trx', $energy_bishu * $v->agent_per_price);
                                            }
                                            
                                            break; //跳出不轮询了
                                        }else{
                                            if($v1->platform_name == 4){
                                                $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                            }
                                            $errorMessage = $errorMessage."能量平台ID：".$v1->rid.$msg;
                                            $save_data = [];
                                            $save_data['comments'] = $time.$errorMessage;
                                            $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $bishuModel->count()) ?'Y':$v->is_notice_admin;
                                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                            continue;
                                        }
                                    }
                                }
                            }else{
                                $save_data = [];
                                $save_data['comments'] = $time.' 无可用能量平台trongas.io,轮询失败';      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            }
                            
                        }else{
                            $save_data = [];
                            $save_data['comments'] = $time.' 笔数不大于0,无需下单:'.$energy_bishu;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                        }
                        
                    //提交给第三方处理,搜狐平台
                    }elseif($v->bishu_daili_type == 3){
                        $time = nowDate();
                        $energy_bishu = $v->max_buy_quantity - $v->total_buy_quantity;
                        
                        if($isAgent == 'Y'){
                            if($agentUser->cash_trx < $energy_bishu * $v->agent_per_price){
                                $errorMessage = "代理地址对应用户TRX余额不足,无法扣款,需要：".($energy_bishu * $v->agent_per_price + 0).",用户余额：".($agentUser->cash_trx + 0);
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }elseif($agentUser->cash_trx >= $energy_bishu * $v->agent_per_price){
                                $isAgent = 'Y';
                            }else{
                                $errorMessage = "代理校验未知错误";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                        }
                        
                        if($energy_bishu > 0){
                            //轮询,自己质押时判断能量是否足够,用平台则判断平台的trx
                            $bishuModel = EnergyPlatform::where('poll_group',$v->poll_group)
                                    ->where('status',0)
                                    ->whereNotNull('platform_apikey')
                                    ->where('platform_name',6)
                                    ->where('platform_balance','>',0)
                                    ->orderBy('seq_sn','desc')
                                    ->get();
                            if($bishuModel->count() > 0){
                                $errorMessage = '';
                                $rsa_services = new RsaServices();
                                $lunxunCount = 0;
                                
                                foreach ($bishuModel as $k1 => $v1){
                                    $lunxunCount = $lunxunCount + 1;
                                    $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);
                                    
                                    if(empty($signstr)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 平台私钥为空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;      //处理备注  
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }
                                    
                                    $payment = env('TRONGASID_BISHU_PAYMENT') ?? 0;
                                    $this->log('handleaienergyorder','提交平台'.$v1->platform_uid.'。提交地址：'.$v->wallet_addr.'。提交次数'.$energy_bishu.'。此时最大次数：'.$v->max_buy_quantity.'。此时已够次数'.$v->total_buy_quantity.'。提交方式：'.$payment);
                                    
                                    $param = [
                                        "token" => $signstr, // api令牌
                                        "times" => $energy_bishu, // 最大委托笔数，当为购买笔数时作为购买笔数的数量
                                        "address" => $v->wallet_addr // 接收资源地址(请勿输入合约地址或没激活地址)
                                    ];
                                    
                                    $balance_url = 'https://admin.weidubot.cc/api/trc_api/delegate_energy_times';
                                    $dlres = post_url($balance_url,$param);
                                    
                                    if(empty($dlres)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 下单失败,接口请求空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $model->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }else{
                                        $this->log('handleaienergyorder','提交地址：'.$v->wallet_addr.'。平台返回：'.json_encode($dlres));
                                        
                                        if((isset($dlres['code']) && $dlres['code'] == 1 && $v1->platform_name == 6)){
                                            // if($v1->platform_name == 6){
                                            //     $orderNo = $dlres['data']['order_sn'];
                                            //     $use_trx = $dlres['data']['amount'];
                                            // }
                                            
                                            $save_data = [];
                                            $save_data['is_buy'] = 'N';      //下单成功
                                            $save_data['comments'] = 'SUCCESS '.$time.' 第三方平台下单,本次次数：'.$energy_bishu;      //处理备注  
                                            $save_data['is_notice'] = 'N';
                                            $save_data['total_buy_quantity'] = $v->max_buy_quantity;
                                            $save_data['last_buy_time'] = $time;
                                            $save_data['energy_platform_rid'] = $v1->rid;
                                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                            
                                            //如果是代理地址，扣代理trx余额
                                            if($isAgent == 'Y'){
                                                $agentUser->decrement('cash_trx', $energy_bishu * $v->agent_per_price);
                                            }
                                            
                                            break; //跳出不轮询了
                                        }else{
                                            if($v1->platform_name == 6){
                                                $msg = ' 下单失败,接口返回:'.json_encode($dlres);
                                            }
                                            $errorMessage = $errorMessage."能量平台ID：".$v1->rid.$msg;
                                            $save_data = [];
                                            $save_data['comments'] = $time.$errorMessage;
                                            $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $bishuModel->count()) ?'Y':$v->is_notice_admin;
                                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                            continue;
                                        }
                                    }
                                }
                            }else{
                                $save_data = [];
                                $save_data['comments'] = $time.' 无可用能量平台搜狐,轮询失败';      //处理备注  
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            }
                            
                        }else{
                            $save_data = [];
                            $save_data['comments'] = $time.' 笔数不大于0,无需下单:'.$energy_bishu;      //处理备注  
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                        }

                    //提交给第三方处理,NL-API平台
                    }elseif($v->bishu_daili_type == 4){
                        $time = nowDate();
                        $energy_bishu = $v->max_buy_quantity - $v->total_buy_quantity;

                        if($isAgent == 'Y'){
                            if($agentUser->cash_trx < $energy_bishu * $v->agent_per_price){
                                $errorMessage = "代理地址对应用户TRX余额不足,无法扣款,需要：".($energy_bishu * $v->agent_per_price + 0).",用户余额：".($agentUser->cash_trx + 0);
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }elseif($agentUser->cash_trx >= $energy_bishu * $v->agent_per_price){
                                $isAgent = 'Y';
                            }else{
                                $errorMessage = "代理校验未知错误";
                                $save_data = [];
                                $save_data['comments'] = $time.$errorMessage;      //处理备注
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                continue;
                            }
                        }

                        if($energy_bishu > 0){
                            $bishuModel = EnergyPlatform::where('poll_group',$v->poll_group)
                                    ->where('status',0)
                                    ->whereNotNull('platform_apikey')
                                    ->where('platform_name',7)
                                    ->where('platform_balance','>',0)
                                    ->orderBy('seq_sn','desc')
                                    ->get();

                            if($bishuModel->count() > 0){
                                $errorMessage = '';
                                $rsa_services = new RsaServices();
                                $lunxunCount = 0;

                                foreach ($bishuModel as $k1 => $v1){
                                    $lunxunCount = $lunxunCount + 1;
                                    $signstr = $rsa_services->privateDecrypt($v1->platform_apikey);

                                    if(empty($signstr)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 平台私钥为空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;      //处理备注
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }

                                    $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
                                    if(empty($nlApiBaseUrl) && !empty($v1->comments)){
                                        if(preg_match('/nl_api_url=([^\s]+)/i', $v1->comments, $matches)){
                                            $nlApiBaseUrl = trim($matches[1]);
                                        }
                                    }
                                    if(empty($nlApiBaseUrl)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." NL-API域名未配置。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $bishuModel->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }

                                    // 笔数套餐在 NL-API 固定按 1 小时 1 笔下发
                                    $day = 0;

                                    $payment = intval(env('NL_API_BISHU_PAYMENT', 0));
                                    $this->log('handleaienergyorder','提交NL-API平台'.$v1->platform_uid.'。提交地址：'.$v->wallet_addr.'。提交次数'.$energy_bishu.'。此时最大次数：'.$v->max_buy_quantity.'。此时已够次数'.$v->total_buy_quantity.'。提交方式：'.$payment);

                                    $param = [
                                        "username" => $v1->platform_uid,
                                        "password" => $signstr,
                                        "receiver_address" => $v->wallet_addr,
                                        "times" => intval($energy_bishu),
                                        "energy" => 65000,
                                        "day" => intval($day),
                                        "hour" => 1,
                                        "payment" => $payment,
                                    ];

                                    $balance_url = rtrim($nlApiBaseUrl, '/') . '/v1/delegate_times';
                                    $header = [
                                        "Content-Type: application/json",
                                        "Accept: application/json"
                                    ];
                                    $dlres = Get_Pay($balance_url, json_encode($param), $header);

                                    if(empty($dlres)){
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid." 下单失败,接口请求空。";
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $bishuModel->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }

                                    if(is_string($dlres)){
                                        $decodeRes = json_decode($dlres,true);
                                        if(!empty($decodeRes)){
                                            $dlres = $decodeRes;
                                        }
                                    }
                                    $this->log('handleaienergyorder','NL-API提交地址：'.$v->wallet_addr.'。平台返回：'.json_encode($dlres, JSON_UNESCAPED_UNICODE));

                                    if(
                                        (isset($dlres['success']) && $dlres['success'] === true) ||
                                        (isset($dlres['code']) && intval($dlres['code']) == 200) ||
                                        isset($dlres['tx_hash']) || isset($dlres['txHash']) ||
                                        isset($dlres['order_id']) || isset($dlres['task_id']) || isset($dlres['id'])
                                    ){
                                        $save_data = [];
                                        $save_data['is_buy'] = 'N';      //下单成功
                                        $save_data['comments'] = 'SUCCESS '.$time.' NL-API平台下单,本次次数：'.$energy_bishu;      //处理备注
                                        $save_data['is_notice'] = 'N';
                                        $save_data['total_buy_quantity'] = $v->max_buy_quantity;
                                        $save_data['last_buy_time'] = $time;
                                        $save_data['energy_platform_rid'] = $v1->rid;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);

                                        if($isAgent == 'Y'){
                                            $agentUser->decrement('cash_trx', $energy_bishu * $v->agent_per_price);
                                        }

                                        break;
                                    }else{
                                        $msg = ' 下单失败,接口返回:'.json_encode($dlres, JSON_UNESCAPED_UNICODE);
                                        $errorMessage = $errorMessage."能量平台ID：".$v1->rid.$msg;
                                        $save_data = [];
                                        $save_data['comments'] = $time.$errorMessage;
                                        $save_data['is_notice_admin'] = ($v->is_notice_admin == 'N' && $lunxunCount >= $bishuModel->count()) ?'Y':$v->is_notice_admin;
                                        EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                                        continue;
                                    }
                                }
                            }else{
                                $save_data = [];
                                $save_data['comments'] = $time.' 无可用能量平台NL-API,轮询失败';      //处理备注
                                EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                            }
                        }else{
                            $save_data = [];
                            $save_data['comments'] = $time.' 笔数不大于0,无需下单:'.$energy_bishu;      //处理备注
                            EnergyAiBishu::where('rid',$v->rid)->update($save_data);
                        }
                    }
                }

            }else{
                // $this->log('handleaienergyorder','----------没有数据----------');
            }
        }catch (\Exception $e){
            $this->log('handleaienergyorder','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
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
