<?php

namespace App\Http\Controllers\Admin\Shop;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Shop\ShopGoods;

class ShopGoodsController extends Controller
{
    public $Status = ['开启','关闭'];
    public $goodsType = ['1'=>'虚拟卡密'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $goodsType = $this->goodsType;

        return view('admin.shop.goods.index',compact("Status","goodsType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = ShopGoods::from('shop_goods as a')
                ->where(function($query) use ($request){
                if ($request->goods_name != '') {
                    $query->where('a.goods_name', 'like' ,"%" . $request->goods_name ."%");
                }   
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*')->orderBy('a.rid','desc')->get();
        
        $goodsType = $this->goodsType;
        
        $data = $data->map(function($query) use ($goodsType){
            $query->goods_type_val = $goodsType[$query->goods_type];
            return $query;
        });
        
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = ShopGoods::where('goods_name', $request->goods_name)->first();
        if(!empty($data)){
            return $this->responseData(400, '商品名称已存在');
        }
        
        $res = ShopGoods::create([
            'goods_name' => $request->goods_name,
            'goods_trx_price' => $request->goods_trx_price ?? 0,
            'goods_usdt_price' => $request->goods_usdt_price ?? 0,
            'goods_type' => $request->goods_type,
            'show_notes' => $request->show_notes ?? '',
            'seq_sn' => $request->seq_sn ?? 0,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = ShopGoods::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = ShopGoods::where('rid', $request->rid)->first();
            $data->goods_name = $request->goods_name;
            $data->goods_trx_price = $request->goods_trx_price ?? 0;
            $data->goods_usdt_price = $request->goods_usdt_price ?? 0;
            $data->goods_type = $request->goods_type;
            $data->seq_sn = $request->seq_sn ?? 0;
            $data->show_notes = $request->show_notes ?? '';
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
    
    // 编辑页面查看
    public function show(Request $request)
    {
        $Status = $this->Status;
        $goodsType = $this->goodsType;
        
        $data = ShopGoods::where('rid',$request->rid)->first();
            
        return view('admin.shop.goods.edit',compact("Status","goodsType","data"));
        
    }
    
    //编辑状态
    public function change_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = ShopGoods::where('rid', $request->rid)->first();
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
