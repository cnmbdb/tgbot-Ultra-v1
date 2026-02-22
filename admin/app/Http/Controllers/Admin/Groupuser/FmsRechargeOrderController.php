<?php

namespace App\Http\Controllers\Admin\Groupuser;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\FmsRechargeOrder;

class FmsRechargeOrderController extends Controller
{
    public $Status = ['0' => '待支付','1' => '已充值','2' => '已过期','3' => '会员取消'];
    
    public function index(Request $request)
    {
        $botData = TelegramBot::pluck('bot_username','rid'); 
        return view('admin.groupuser.rechargeorder.index',compact("botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = FmsRechargeOrder::from('fms_recharge_order as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->recharge_tg_username != '') {
                    $query->where('a.recharge_tg_username', 'like' ,"%" . $request->recharge_tg_username ."%");
                }
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $Status = $this->Status;
        
        $data = $data->map(function($query) use ($Status){
            $query->status_val = $Status[$query->status];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
}
