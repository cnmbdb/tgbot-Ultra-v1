<?php

namespace App\Http\Controllers\Admin\Shop;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Shop\ShopOrder;

class ShopOrderController extends Controller
{
    public $payType = ["1" => "TRX 余额","2" => "USDT 余额"];
    
    public function index(Request $request)
    {
        $botData = TelegramBot::pluck('bot_username','rid'); 

        return view('admin.shop.order.index',compact("botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = ShopOrder::from('shop_order as a')
                ->join('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->cdkey_no != '') {
                    $query->where('a.cdkey_no', 'like' ,"%" . $request->cdkey_no ."%");
                }   
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $payType = $this->payType;
        
        $data = $data->map(function($query) use ($payType){
            $query->pay_type_val = $payType[$query->pay_type];
            return $query;
        });
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }

    //编辑
    public function update(Request $request)
    {
        $data = ShopOrder::where('rid',$request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '订单不存在');
        }
        
        DB::beginTransaction();
        try {
            $data->comments = $request->comments;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
}
