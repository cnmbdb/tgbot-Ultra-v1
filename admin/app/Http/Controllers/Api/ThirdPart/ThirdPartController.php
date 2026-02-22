<?php

namespace App\Http\Controllers\Api\ThirdPart;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBotUser;
use App\Models\Energy\EnergyAiBishu;
use App\Models\Energy\EnergyThirdPart;

class ThirdPartController extends Controller
{
    public $thirdPlatformId = 1; //使用能量平台ID下单,只能是trongas.io对应的平台
    public $thirdBotId = 1; //使用机器人ID
    public $bishuTrxPrice = 2.5; //笔数trx一笔价格
    public $shanzu32000TrxPrice = 2.5; //闪租3200 trx一笔1小时价格
    public $shanzu65000TrxPrice = 2.9; //闪租65000 trx一笔1小时价格
    
    //查询余额
    public function balance(Request $request)
    {
        $data = TelegramBotUser::where('tg_uid',$request->tg_uid)->where('bot_rid',$this->thirdBotId)->first();
        if($data){
            $returnData = [];
            $returnData['trx_balance'] = $data->cash_trx;
            $returnData['usdt_balance'] = $data->cash_usdt;
            return $this->responseData(200, 'success', $returnData);
        }else{
            return $this->responseData(400, '用户信息不存在,请配置正确的用户ID');
        }
    }
    
    //笔数下单
    public function bishuorder(Request $request)
    {
        if(empty($request->maxDelegateNums) || $request->maxDelegateNums <= 0 || mb_strlen($request->receiveAddress) != 34){
            return $this->responseData(400, '笔数模式下单错误,检查参数');
        }
        
        $data = TelegramBotUser::where('tg_uid',$request->tg_uid)->where('bot_rid',$this->thirdBotId)->first();
        if($data){
            DB::beginTransaction();
            try {
                //判断trx余额是否足够
                if($request->maxDelegateNums * $this->bishuTrxPrice > $data->cash_trx){
                    return $this->responseData(400, '用户trx余额不足,当前:'.$data->cash_trx.',需要:'.($request->maxDelegateNums * $this->bishuTrxPrice));
                }
                
                //扣除用户余额
                TelegramBotUser::where('tg_uid',$request->tg_uid)->where('bot_rid',$this->thirdBotId)->update(['cash_trx' => $data->cash_trx - ($request->maxDelegateNums * $this->bishuTrxPrice)]);
                
                $time = nowDate();
                
                //记录笔数
                $energyAiBishu = EnergyAiBishu::where('wallet_addr',$request->receiveAddress)->first();
                if($energyAiBishu){
                    $save_data = [];
                    $save_data['max_buy_quantity'] = $energyAiBishu->max_buy_quantity + $request->maxDelegateNums;
                    EnergyAiBishu::where('rid',$energyAiBishu->rid)->update($save_data);
                    
                }else{
                    $insert_data = [];
                    $insert_data['bot_rid'] = $this->thirdBotId;
                    $insert_data['wallet_addr'] = $request->receiveAddress;
                    $insert_data['status'] = 0;
                    $insert_data['total_buy_usdt'] = 0;
                    $insert_data['max_buy_quantity'] = $request->maxDelegateNums;
                    $insert_data['create_time'] = $time;
                    EnergyAiBishu::insert($insert_data);
                }
                
                //记录提交明细
                $insert_data = [];
                $insert_data['bot_rid'] = $this->thirdBotId;
                $insert_data['wallet_addr'] = $request->receiveAddress;
                $insert_data['order_type'] = 1;
                $insert_data['tg_uid'] = $request->tg_uid;
                $insert_data['platform_rid'] = $this->thirdPlatformId;
                $insert_data['cishu_energy'] = $request->maxDelegateNums;
                $insert_data['before_trx'] = $data->cash_trx;
                $insert_data['change_trx'] = $request->maxDelegateNums * $this->bishuTrxPrice;
                $insert_data['after_trx'] = $data->cash_trx - ($request->maxDelegateNums * $this->bishuTrxPrice);
                $insert_data['order_time'] = $time;
                $insert_data['process_status'] = 9; //无需额外处理下单
                EnergyThirdPart::insert($insert_data);
                
                $returnData['orderId'] = createNo();
                $returnData['orderMoney'] = $request->maxDelegateNums * $this->bishuTrxPrice;
                
                DB::commit();
                return $this->responseData(200, 'success', $returnData);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->responseData(400, '下单失败'.$e->getMessage());
            }
            
        }else{
            return $this->responseData(400, '用户信息不存在,请配置正确的用户ID');
        }
    }
    
    //闪租下单
    public function shanzuorder(Request $request)
    {
        if(empty($request->payNums) || $request->payNums < 65000 || mb_strlen($request->receiveAddress) != 34 || $request->rentTime != 1){
            return $this->responseData(400, '闪租模式下单错误,检查参数');
        }
        
        $data = TelegramBotUser::where('tg_uid',$request->tg_uid)->where('bot_rid',$this->thirdBotId)->first();
        if($data){
            DB::beginTransaction();
            try {
                if($request->payNums == 131000){
                    $kouTrx = $this->shanzu65000TrxPrice;
                }else{
                    $kouTrx = ceil($request->payNums / 65000) * $this->shanzu32000TrxPrice;
                }
                
                //判断trx余额是否足够
                if($kouTrx > $data->cash_trx){
                    return $this->responseData(400, '用户trx余额不足,当前:'.$data->cash_trx.',需要:'.$kouTrx);
                }
                
                //扣除用户余额
                TelegramBotUser::where('tg_uid',$request->tg_uid)->where('bot_rid',$this->thirdBotId)->update(['cash_trx' => $data->cash_trx - $kouTrx]);
                
                $time = nowDate();
                
                //记录提交明细,从这个里面下单
                $insert_data = [];
                $insert_data['bot_rid'] = $this->thirdBotId;
                $insert_data['wallet_addr'] = $request->receiveAddress;
                $insert_data['order_type'] = 2;
                $insert_data['tg_uid'] = $request->tg_uid;
                $insert_data['platform_rid'] = $this->thirdPlatformId;
                $insert_data['cishu_energy'] = $request->payNums;
                $insert_data['before_trx'] = $data->cash_trx;
                $insert_data['change_trx'] = $kouTrx;
                $insert_data['after_trx'] = $data->cash_trx - $kouTrx;
                $insert_data['order_time'] = $time;
                $insert_data['process_status'] = 1; //需要处理下单
                EnergyThirdPart::insert($insert_data);
                
                $returnData['orderId'] = createNo();
                $returnData['orderMoney'] = $kouTrx;
                
                DB::commit();
                return $this->responseData(200, 'success', $returnData);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->responseData(400, '下单失败'.$e->getMessage());
            }
            
        }else{
            return $this->responseData(400, '用户信息不存在,请配置正确的用户ID');
        }
    }
}
