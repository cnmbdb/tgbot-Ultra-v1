<?php

namespace App\Http\Controllers\Admin\Shop;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Shop\ShopGoods;
use App\Models\Shop\ShopGoodsBot;
use App\Models\Telegram\TelegramBot;

class ShopGoodsBotController extends Controller
{
    public $Status = ['开启','关闭'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $ShopGoods = ShopGoods::pluck('goods_name','rid'); 
        $botData = TelegramBot::pluck('bot_username','rid'); 

        return view('admin.shop.bot.index',compact("Status","ShopGoods","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = ShopGoodsBot::from('t_shop_goods_bot as a')
                ->join('t_shop_goods as b','a.goods_rid','b.rid')
                ->join('t_telegram_bot as c','a.bot_rid','c.rid')
                ->where(function($query) use ($request){
                if ($request->goods_name != '') {
                    $query->where('b.goods_name', 'like' ,"%" . $request->goods_name ."%");
                }   
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.goods_name','c.bot_token','c.bot_firstname','c.bot_username')->orderBy('a.rid','desc')->get();
        
        $Status = $this->Status;
        
        $data = $data->map(function($query) use ($Status){
            $query->status_val = $Status[$query->status];
            return $query;
        });
        
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = ShopGoodsBot::where('goods_rid', $request->goods_rid)->where('bot_rid', $request->bot_rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人商品名称已存在');
        }
        
        if($request->goods_usdt_discount <= 0 || $request->goods_usdt_discount > 1 || $request->goods_trx_discount <= 0 || $request->goods_trx_discount > 1){
            return $this->responseData(400, '折扣只能大于0小于等于1');
        }
        
        $res = ShopGoodsBot::create([
            'goods_rid' => $request->goods_rid,
            'bot_rid' => $request->bot_rid,
            'goods_usdt_discount' => $request->goods_usdt_discount ?? 1,
            'goods_trx_discount' => $request->goods_trx_discount ?? 1,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = ShopGoodsBot::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = ShopGoodsBot::where('goods_rid', $request->goods_rid)->where('bot_rid', $request->bot_rid)->where('rid', '<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人商品名称已存在');
        }
        if($request->goods_usdt_discount <= 0 || $request->goods_usdt_discount > 1 || $request->goods_trx_discount <= 0 || $request->goods_trx_discount > 1){
            return $this->responseData(400, '折扣只能大于0小于等于1');
        }
        
        DB::beginTransaction();
        try {
            $data = ShopGoodsBot::where('rid', $request->rid)->first();
            $data->goods_rid = $request->goods_rid;
            $data->bot_rid = $request->bot_rid;
            $data->goods_usdt_discount = $request->goods_usdt_discount ?? 1;
            $data->goods_trx_discount = $request->goods_trx_discount ?? 1;
            $data->comments = $request->comments ?? '';
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
            $data = ShopGoodsBot::where('rid', $request->rid)->first();
            $data->status = $request->status == 1 ? 0 : 1;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
}
