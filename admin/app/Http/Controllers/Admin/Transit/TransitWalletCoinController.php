<?php

namespace App\Http\Controllers\Admin\Transit;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Transit\TransitWallet;
use App\Models\Transit\TransitWalletCoin;
use App\Http\Services\RsaServices;

class TransitWalletCoinController extends Controller
{
    public $Coinname = ['trx' => 'trx','usdt' => 'usdt'];
    public $IsRealtimeRate = ['1' => '实时(直减)','2' => '固定','3' => '实时(百分比)'];

    public function index(Request $request)
    {
        $Coinname = $this->Coinname;
        $IsRealtimeRate = $this->IsRealtimeRate;
        
        $walletData = TransitWallet::pluck('receive_wallet','rid'); 

        return view('admin.transit.walletcoin.index',compact("Coinname","walletData","IsRealtimeRate"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TransitWalletCoin::from('t_transit_wallet_coin as a')
                 ->join('t_transit_wallet as b','a.transit_wallet_id','b.rid')
                 ->where(function($query) use ($request){
                    if ($request->receive_wallet != '') {
                        $query->where('receive_wallet', 'like' ,"%" . $request->receive_wallet ."%");
                    }   
                    if ($request->send_wallet != '') {
                        $query->where('send_wallet', 'like' ,"%" . $request->send_wallet ."%");
                    }  
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.receive_wallet')->orderBy('a.rid','desc')->get();
        
        $data = $data->map(function($query){
            $query->is_realtime_rate_val = $this->IsRealtimeRate[$query->is_realtime_rate];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        llog('--添加闪兑币种汇率--');
        llog($request->All());
        if($request->profit_rate < 0 || $request->profit_rate >= 1 || $request->exchange_rate > 10 || $request->exchange_rate < 0 || $request->kou_out_amount < 0){
            return $this->responseData(400, '汇率不能大于10且扣回款金额不能小于0');
        }
        
        $data = TransitWalletCoin::where('transit_wallet_id', $request->transit_wallet_id)->where('in_coin_name', $request->in_coin_name)->first();
        if(!empty($data)){
            return $this->responseData(400, '闪兑钱包币种已存在');
        }
        
        if($request->in_coin_name == $request->out_coin_name){
            return $this->responseData(400, '转入和回款币名不能一样');
        }

        $res = TransitWalletCoin::create([
            'transit_wallet_id' => $request->transit_wallet_id,
            'in_coin_name' => $request->in_coin_name,
            'out_coin_name' => $request->out_coin_name,
            'is_realtime_rate' => $request->is_realtime_rate,
            'profit_rate' => $request->profit_rate ?? 0.1,
            'exchange_rate' => $request->exchange_rate ?? 1,
            'kou_out_amount' => $request->kou_out_amount ?? 0,
            'min_transit_amount' => $request->min_transit_amount ?? 1,
            'max_transit_amount' => $request->max_transit_amount ?? 1000,
            'comments' => $request->comments,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TransitWalletCoin::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        llog('--修改闪兑币种汇率--');
        llog($request->All());
        if($request->profit_rate < 0 || $request->profit_rate >= 1 || $request->exchange_rate > 10 || $request->exchange_rate < 0 || $request->kou_out_amount < 0){
            return $this->responseData(400, '汇率不能大于10且扣回款金额不能小于0');
        }
        
        $data = TransitWalletCoin::where('transit_wallet_id', $request->transit_wallet_id)->where('in_coin_name', $request->in_coin_name)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '闪兑钱包币种已存在');
        }
        
        if($request->in_coin_name == $request->out_coin_name){
            return $this->responseData(400, '转入和回款币名不能一样');
        }
        
        DB::beginTransaction();
        try {
            $data = TransitWalletCoin::where('rid', $request->rid)->first();
            $data->in_coin_name = $request->in_coin_name;
            $data->out_coin_name = $request->out_coin_name;
            $data->is_realtime_rate = $request->is_realtime_rate;
            $data->profit_rate = $request->profit_rate ?? 0.1;
            $data->exchange_rate = $request->exchange_rate ?? 1;
            $data->kou_out_amount = $request->kou_out_amount ?? 0;
            $data->min_transit_amount = $request->min_transit_amount ?? 1;
            $data->max_transit_amount = $request->max_transit_amount ?? 1000;
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
