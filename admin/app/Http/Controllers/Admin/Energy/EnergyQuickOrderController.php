<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyQuickOrder;
use App\Models\Telegram\TelegramBot;

class EnergyQuickOrderController extends Controller
{
    public $status = ['6' => '能量钱包未启用','7' => '金额无对应套餐','8' => '下单中','9' => '下单成功','1' => '待下单','5' => '能量钱包未配置私钥','4' => '下单失败'];
    
    public function index(Request $request)
    {
        $status = $this->status;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.quick.index',compact("status","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyQuickOrder::from('t_energy_quick_order as a')
                ->join('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
            if ($request->bot_rid != '') {
                    $query->where('b.bot_rid', $request->bot_rid);
                }
            if ($request->wallet_addr != '') {
                $query->where('wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
            } 
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $data = $data->map(function($query){
            $query->status = $this->status[$query->status];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //重新补发
    public function reorder(Request $request)
    {   
        $data = EnergyQuickOrder::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if(!in_array($data->status,[4,5,6,7])){
            return $this->responseData(400, '状态为-失败，才能补发');
        }
        
        DB::beginTransaction();
        try {
            $data->status = 1; //重新处理
            $data->save();
            DB::commit();
            return $this->responseData(200, '补发成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '补发失败'.$e->getMessage());
        }
        
    }

}
