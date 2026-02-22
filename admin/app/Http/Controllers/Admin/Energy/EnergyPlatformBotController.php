<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Premium\PremiumPlatform;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Transit\TransitWallet;

class EnergyPlatformBotController extends Controller
{
    public $Status = ['开启','关闭'];
    public $IsOpenAiTrusteeship = ['Y' => '开启','N' => '关闭'];
    public $AiEnergyDay = ['0' => '1小时','1' => '1天','3' => '3天'];
    public $EnergyDay = ['0' => '1小时','1' => '1天','3' => '3天','30' => '30天'];
    public $PollGroup = ['A' => 'A组','B' => 'B组','C' => 'C组','D' => 'D组','E' => 'E组','F' => 'F组','G' => 'G组'];
    public $BishuRecoveryType = ['1' => '到期回收','2' => '代理下一笔回收'];
    public $AiRecoveryType = ['1' => '到期回收','2' => '代理下一笔回收'];
    public $BishuDailiType = ['1' => '自动','2' => '提交到trongas.io','3' => '提交到搜狐'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $IsOpenAiTrusteeship = $this->IsOpenAiTrusteeship;
        $EnergyDay = $this->EnergyDay;
        $PollGroup = $this->PollGroup;
        $BishuRecoveryType = $this->BishuRecoveryType;
        $AiEnergyDay = $this->AiEnergyDay;
        $BishuDailiType = $this->BishuDailiType;
        $AiRecoveryType = $this->AiRecoveryType;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.platformbot.index',compact("Status","botData","IsOpenAiTrusteeship","EnergyDay","PollGroup","BishuRecoveryType","AiEnergyDay","BishuDailiType","AiRecoveryType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyPlatformBot::from('energy_platform_bot as a')
                 ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                 ->where(function($query) use ($request){
                if ($request->platform_uid != '') {
                    $query->where('a.platform_uid', 'like' ,"%" . $request->platform_uid ."%");
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $PollGroup = $this->PollGroup;
        $IsOpenAiTrusteeship = $this->IsOpenAiTrusteeship;
        
        $data = $data->map(function($query) use ($PollGroup,$IsOpenAiTrusteeship){
            $query->poll_group_val = $PollGroup[$query->poll_group];
            $query->is_open_ai_trusteeship_val = $IsOpenAiTrusteeship[$query->is_open_ai_trusteeship];
            $query->is_open_bishu_val = $IsOpenAiTrusteeship[$query->is_open_bishu];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = EnergyPlatformBot::where('bot_rid', $request->bot_rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人对应能量平台已存在');
        }
        $premiumdata = PremiumPlatform::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($premiumdata)){
            return $this->responseData(400, '不能和会员钱包地址一致');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $transitdata = TransitWallet::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($transitdata)){
            return $this->responseData(400, '不能和闪兑钱包地址一致');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '能量钱包地址已存在');
        }
        
        if(!empty($request->agent_tg_uid) && $request->agent_per_price <= 0){
            return $this->responseData(400, '代理trx价格必须大于0');
        }
        
        $res = EnergyPlatformBot::create([
            'bot_rid' => $request->bot_rid,
            'poll_group' => $request->poll_group,
            'tg_admin_uid' => $request->tg_admin_uid ?? '',
            'tg_notice_obj_receive' => $request->tg_notice_obj_receive ?? '',
            'tg_notice_obj_send' => $request->tg_notice_obj_send ?? '',
            'receive_wallet' => $request->receive_wallet ?? '',
            'get_tx_time' => $request->get_tx_time ?? null,
            'comments' => $request->comments ?? '',
            'bishu_stop_day' => $request->bishu_stop_day ?? 0,
            'agent_tg_uid' => (empty($request->agent_tg_uid) || $request->agent_tg_uid == 'null') ?NULL:$request->agent_tg_uid,
            'agent_per_price' => (empty($request->agent_per_price) || $request->agent_per_price == 'null') ?NULL:$request->agent_per_price,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyPlatformBot::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $premiumdata = PremiumPlatform::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($premiumdata)){
            return $this->responseData(400, '不能和会员钱包地址一致');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $transitdata = TransitWallet::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($transitdata)){
            return $this->responseData(400, '不能和闪兑钱包地址一致');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->where('rid', '<>',$request->rid)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '能量钱包地址已存在');
        }
        
        if(!empty($request->agent_tg_uid) && $request->agent_tg_uid != 'null' && $request->agent_per_price <= 0){
            return $this->responseData(400, '代理trx价格必须大于0');
        }
        
        DB::beginTransaction();
        try {
            $data = EnergyPlatformBot::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->poll_group = $request->poll_group;
            $data->tg_admin_uid = $request->tg_admin_uid ?? '';
            $data->tg_notice_obj_receive = $request->tg_notice_obj_receive ?? '';
            $data->tg_notice_obj_send = $request->tg_notice_obj_send ?? '';
            $data->receive_wallet = $request->receive_wallet ?? '';
            $data->get_tx_time = $request->get_tx_time ?? null;
            $data->comments = $request->comments ?? '';
            $data->agent_tg_uid = (empty($request->agent_tg_uid) || $request->agent_tg_uid == 'null') ?NULL:$request->agent_tg_uid;
            $data->agent_per_price = (empty($request->agent_per_price) || $request->agent_per_price == 'null') ?NULL:$request->agent_per_price;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑状态
    public function change_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = EnergyPlatformBot::where('rid', $request->rid)->first();
            $data->status = $request->status == 1 ? 0 : 1;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //智能托管
    public function aitrusteeship(Request $request)
    {
        if($request->is_open_ai_trusteeship == 'Y' && ($request->trx_price_energy_32000 <= 0 || $request->trx_price_energy_65000 <= 0)){
            return $this->responseData(400, '价格必须大于0');
        }
        
        DB::beginTransaction();
        try {
            $data = EnergyPlatformBot::where('rid', $request->rid)->first();
            $data->is_open_ai_trusteeship = $request->is_open_ai_trusteeship;
            $data->trx_price_energy_32000 = $request->trx_price_energy_32000;
            $data->trx_price_energy_65000 = $request->trx_price_energy_65000;
            $data->per_energy_day = $request->per_energy_day;
            $data->ai_trusteeship_recovery_type = $request->ai_trusteeship_recovery_type;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //笔数套餐
    public function bishu(Request $request)
    {
        if($request->is_open_bishu == 'Y' && $request->per_bishu_usdt_price <= 0){
            return $this->responseData(400, '价格必须大于0');
        }
        
        if(!in_array($request->per_bishu_energy_quantity,[65000,131000])){
            return $this->responseData(400, '每笔能量应为65000或者131000');
        }
        
        DB::beginTransaction();
        try {
            $data = EnergyPlatformBot::where('rid', $request->rid)->first();
            $data->is_open_bishu = $request->is_open_bishu;
            $data->per_bishu_usdt_price = $request->per_bishu_usdt_price;
            $data->per_bishu_energy_quantity = $request->per_bishu_energy_quantity;
            $data->per_energy_day_bishu = $request->per_energy_day_bishu;
            $data->bishu_recovery_type = $request->bishu_recovery_type;
            $data->bishu_daili_type = $request->bishu_daili_type;
            $data->bishu_stop_day = $request->bishu_stop_day ?? 0;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
}
