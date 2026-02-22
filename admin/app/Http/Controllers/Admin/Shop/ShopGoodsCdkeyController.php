<?php

namespace App\Http\Controllers\Admin\Shop;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Shop\ShopGoods;
use App\Models\Shop\ShopGoodsCdkey;
use App\Http\Services\RsaServices;

class ShopGoodsCdkeyController extends Controller
{
    public $Status = ['待上架','售卖中','已售卖'];

    public function index(Request $request)
    {
        $Status = $this->Status;
        $ShopGoods = ShopGoods::pluck('goods_name','rid'); 

        return view('admin.shop.cdkey.index',compact("Status","ShopGoods"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = ShopGoodsCdkey::from('t_shop_goods_cdkey as a')
                ->join('t_shop_goods as b','a.goods_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->cdkey_no != '') {
                    $query->where('a.cdkey_no', 'like' ,"%" . $request->cdkey_no ."%");
                }  
                if ($request->goods_rid != '') {
                    $query->where('a.goods_rid', $request->goods_rid);
                }  
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.goods_name')->orderBy('a.rid','desc')->get();
        
        $Status = $this->Status;
        $rsa_services = new RsaServices();
        
        $data = $data->map(function($query) use ($Status,$rsa_services){
            $query->status_val = $Status[$query->status];
            $query->cdkey_pwd_en = str_replace("\r", "", $rsa_services->privateDecrypt($query->cdkey_pwd));        //解密
            $query->cdkey_pwd = mb_substr($query->cdkey_pwd_en, 0,4).'****'.mb_substr($query->cdkey_pwd_en, -4,4);
            return $query;
        });
        
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    // 页面查看
    public function show(Request $request)
    {
        return $this->responseData(200, '成功');
        
    }
    
    //添加
    public function add(Request $request)
    {
        $data = ShopGoodsCdkey::where('cdkey_no', $request->cdkey_no)->first();
        if(!empty($data)){
            return $this->responseData(400, '卡号已存在');
        }
        
        $rsa_services = new RsaServices();
        $PRIVATE_KEY = $rsa_services->publicEncrypt($request->cdkey_pwd);
        $res = ShopGoodsCdkey::create([
            'goods_rid' => $request->goods_rid,
            'cdkey_no' => $request->cdkey_no,
            'cdkey_pwd' => $PRIVATE_KEY,
            'cdkey_trx_price' => $request->cdkey_trx_price ?? 0,
            'cdkey_usdt_price' => $request->cdkey_usdt_price ?? 0,
            'seq_sn' => $request->seq_sn ?? 0,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //批量添加
    public function batchadd(Request $request)
    {
        $explodeArr = explode("\n", $request->cdkey_no);
        
        if(count($explodeArr) > 0){
            
            $rsa_services = new RsaServices();
            for ($i = 0; $i < count($explodeArr); $i++) {
                $cdkey = explode(',',$explodeArr[$i]);
                $cdkey_no = str_replace("\n", "", $cdkey[0]);
                $cdkey_pwd = str_replace("\n", "", $cdkey[1]);
                
                $data = ShopGoodsCdkey::where('cdkey_no', $cdkey_no)->first();
                if(!empty($data)){
                    continue;
                }
                
                $res = ShopGoodsCdkey::create([
                    'goods_rid' => $request->goods_rid,
                    'cdkey_no' => $cdkey_no,
                    'cdkey_pwd' => $rsa_services->publicEncrypt($cdkey_pwd),
                    'cdkey_trx_price' => $request->cdkey_trx_price ?? 0,
                    'cdkey_usdt_price' => $request->cdkey_usdt_price ?? 0,
                    'seq_sn' => $request->seq_sn ?? 0,
                    'create_time' => nowDate()
                ]);
            }
            return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
        }else{
            return $res ? $this->responseData(200, '数据为空') : $this->responseData(400, '添加失败');
        }
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = ShopGoodsCdkey::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $ecdkeydata = ShopGoodsCdkey::where('cdkey_no', $request->cdkey_no)->where('rid', '<>',$request->rid)->first();
        if(!empty($ecdkeydata)){
            return $this->responseData(400, '卡号已存在');
        }
        
        $rsa_services = new RsaServices();
        $PRIVATE_KEY = $rsa_services->publicEncrypt($request->cdkey_pwd);
        DB::beginTransaction();
        try {
            $data = ShopGoodsCdkey::where('rid', $request->rid)->first();
            $data->goods_rid = $request->goods_rid;
            $data->cdkey_trx_price = $request->cdkey_trx_price ?? 0;
            $data->cdkey_usdt_price = $request->cdkey_usdt_price ?? 0;
            $data->cdkey_no = $request->cdkey_no;
            $data->cdkey_pwd = $PRIVATE_KEY;
            $data->status = $request->status;
            $data->seq_sn = $request->seq_sn ?? 0;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //批量上架
    public function batchshang(Request $request)
    {
        $data = ShopGoodsCdkey::where('goods_rid', $request->goods_rid)->get();
        if($data->count() == 0){
            return $this->responseData(400, '商品不存在卡密');
        }
        
        DB::beginTransaction();
        try {
            ShopGoodsCdkey::where('goods_rid', $request->goods_rid)->where('status',0)->update(['status' => 1]);
            DB::commit();
            return $this->responseData(200, '上架成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '上架失败'.$e->getMessage());
        }
    }
    
    //批量下架
    public function batchxia(Request $request)
    {
        $data = ShopGoodsCdkey::where('goods_rid', $request->goods_rid)->get();
        if($data->count() == 0){
            return $this->responseData(400, '商品不存在卡密');
        }
        
        DB::beginTransaction();
        try {
            ShopGoodsCdkey::where('goods_rid', $request->goods_rid)->where('status',1)->update(['status' => 0]);
            DB::commit();
            return $this->responseData(200, '上架成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '上架失败'.$e->getMessage());
        }
    }
}
