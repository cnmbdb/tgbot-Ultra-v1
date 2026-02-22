<?php

namespace App\Http\Controllers\Admin\Transit;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Transit\TransitWalletBlack;

class TransitWalletblackController extends Controller
{
    public $ChainType = ['trc' => 'trc'];
    
    public function index(Request $request)
    {
        $ChainType = $this->ChainType;
        return view('admin.transit.walletblack.index',compact("ChainType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TransitWalletBlack::where(function($query) use ($request){
            if ($request->black_wallet != '') {
                $query->where('black_wallet', 'like' ,"%" . $request->black_wallet ."%");
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
        $data = TransitWalletBlack::where('black_wallet', $request->black_wallet)->where('chain_type', $request->chain_type)->first();
        if(!empty($data)){
            return $this->responseData(400, '黑钱包已存在');
        }
        $res = TransitWalletBlack::create([
            'chain_type' => $request->chain_type,
            'black_wallet' => $request->black_wallet,
            'comments' => $request->comments,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TransitWalletBlack::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TransitWalletBlack::where('black_wallet', $request->black_wallet)->where('chain_type', $request->chain_type)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '黑钱包已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = TransitWalletBlack::where('rid', $request->rid)->first();
            $data->chain_type = $request->chain_type;
            $data->black_wallet = $request->black_wallet;
            $data->comments = $request->comments;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }
}
