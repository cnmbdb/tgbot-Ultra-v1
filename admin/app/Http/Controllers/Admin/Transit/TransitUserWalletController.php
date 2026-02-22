<?php

namespace App\Http\Controllers\Admin\Transit;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Transit\TransitUserWallet;

class TransitUserWalletController extends Controller
{
    public $ChainType = ['trc' => 'trc'];
    
    public function index(Request $request)
    {
        $ChainType = $this->ChainType;
        return view('admin.transit.userwallet.index',compact("ChainType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TransitUserWallet::where(function($query) use ($request){
            if ($request->wallet_addr != '') {
                $query->where('wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
            }   
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->orderBy('rid','desc')->get();

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TransitUserWallet::where('wallet_addr', $request->wallet_addr)->where('chain_type', $request->chain_type)->first();
        if(!empty($data)){
            return $this->responseData(400, '钱包已存在');
        }
        $res = TransitUserWallet::create([
            'chain_type' => $request->chain_type,
            'wallet_addr' => $request->wallet_addr,
            'total_yuzhi_sxf' => $request->yuzhi_sxf,
            'need_feedback_sxf' => $request->yuzhi_sxf,
            'last_yuzhi_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TransitUserWallet::where('wallet_addr', $request->wallet_addr)->where('chain_type', $request->chain_type)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '钱包已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = TransitUserWallet::where('rid', $request->rid)->first();
            $data->total_yuzhi_sxf = $data->total_yuzhi_sxf + $request->yuzhi_sxf;
            $data->need_feedback_sxf = $request->yuzhi_sxf;
            $data->last_yuzhi_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }
}
