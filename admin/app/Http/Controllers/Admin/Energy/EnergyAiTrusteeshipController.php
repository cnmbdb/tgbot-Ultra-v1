<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Energy\EnergyAiTrusteeship;

class EnergyAiTrusteeshipController extends Controller
{
    public $Status = ['0' => '开启','1' => '关闭','2' => '管理员关闭'];
    public $isBuy = ['Y' => '需要下单','N' => '无需下单','B' => '下单中'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        return view('admin.energy.aitrusteeship.index',compact("Status","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyAiTrusteeship::from('energy_ai_trusteeship as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->tg_uid != '') {
                    $query->where('tg_uid', 'like' ,"%" . $request->tg_uid ."%");
                }   
                if ($request->wallet_addr != '') {
                    $query->where('wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
                }  
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $data = $data->map(function($query){
            $query->status_val = $this->Status[$query->status];
            $query->is_buy_val = $this->isBuy[$query->is_buy];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    
    //编辑
    public function update(Request $request)
    {
        if(empty($request->per_buy_energy_quantity) || $request->per_buy_energy_quantity < 65000){
            return $this->responseData(400, '每次购买能量数量需大于等于65000');
        }
        
        if(empty($request->min_energy_quantity) || $request->min_energy_quantity <= 0){
            return $this->responseData(400, '能量低于值购买需大于0');
        }
        
        if(empty($request->wallet_addr)){
            return $this->responseData(400, '钱包地址不能为空');
        }
        
        if(empty($request->tg_uid)){
            return $this->responseData(400, 'tg用户ID不能为空');
        }
        
        $dataexist = EnergyAiTrusteeship::where('wallet_addr',$request->wallet_addr)->where('rid','<>',$request->rid)->first();
        if(!empty($dataexist)){
            return $this->responseData(400, '钱包地址已存在');
        }
        
        $data = EnergyAiTrusteeship::where('rid',$request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        DB::beginTransaction();  
        try {
            $data->status = $request->status;
            $data->wallet_addr = $request->wallet_addr;
            $data->tg_uid = $request->tg_uid;
            $data->min_energy_quantity = $request->min_energy_quantity;
            $data->per_buy_energy_quantity = $request->per_buy_energy_quantity;
            $data->back_comments = $request->back_comments;
            $data->max_buy_quantity = $request->max_buy_quantity ?? 0;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyAiTrusteeship::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
    
    //刷新
    public function refresh(Request $request)
    {
        $res = EnergyAiTrusteeship::where('rid', $request->rid)->update(['is_buy' => 'N']);
        return $res ? $this->responseData(200, '刷新成功,请等待更新') : $this->responseData(400, '刷新失败');
    }

}
