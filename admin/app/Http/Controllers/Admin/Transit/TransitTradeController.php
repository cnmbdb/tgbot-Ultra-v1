<?php

namespace App\Http\Controllers\Admin\Transit;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Transit\TransitWalletTradeList;

class TransitTradeController extends Controller
{
    public $ProcessStatus = ['6' => '黑钱包','7' => '转入金额不符','8' => '转帐中','9' => '转账成功','1' => '待兑换','10' => '余额不足','5' => '币种无效','2' => '交易失败','0' => '待确认'];
    
    public function index(Request $request)
    {
        return view('admin.transit.trade.index');
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TransitWalletTradeList::where(function($query) use ($request){
            if ($request->tx_hash != '') {
                $query->where('tx_hash', 'like' ,"%" . $request->tx_hash ."%");
            }   
            if ($request->transferfrom_address != '') {
                $query->where('transferfrom_address', 'like' ,"%" . $request->transferfrom_address ."%");
            }  
            if ($request->transferto_address != '') {
                $query->where('transferto_address', 'like' ,"%" . $request->transferto_address ."%");
            } 
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->orderBy('rid','desc')->get();
        
        $data = $data->map(function($query){
            $query->process_status = $this->ProcessStatus[$query->process_status];
            $query->timestamp = date('Y-m-d H:i:s', floor($query->timestamp / 1000));
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //重新补发
    public function reswap(Request $request)
    {   
        $data = TransitWalletTradeList::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if($data->process_status <> 10){
            return $this->responseData(400, '状态为-余额不足时，才能补发');
        }
        
        DB::beginTransaction();
        try {
            $data->process_status = 1; //重新处理
            $data->tg_notice_status_send = 'N';
            $data->save();
            DB::commit();
            return $this->responseData(200, '补发成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '补发失败'.$e->getMessage());
        }
    }
    
    //禁止发放
    public function stopswap(Request $request)
    {   
        $data = TransitWalletTradeList::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if(!in_array($data->process_status,[1,10])){
            return $this->responseData(400, '状态为-余额不足或待兑换，才能禁止');
        }
        
        DB::beginTransaction();
        try {
            $data->process_status = 5; //币种无效
            $data->tg_notice_status_send = 'Y';
            $data->process_comments = '人工禁止兑换';
            $data->save();
            DB::commit();
            return $this->responseData(200, '禁止成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '禁止失败'.$e->getMessage());
        }
    }

}
