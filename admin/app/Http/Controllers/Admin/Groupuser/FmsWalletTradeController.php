<?php

namespace App\Http\Controllers\Admin\Groupuser;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\FmsWalletTradeList;

class FmsWalletTradeController extends Controller
{
    public $ProcessStatus = ['1' => '待充值','7' => '金额无对应订单','8' => '充值中','9' => '充值成功','4' => '充值失败','2' => '人工禁止','3' => '找不到用户'];
    
    public function index(Request $request)
    {
        return view('admin.groupuser.rechargetrade.index');
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = FmsWalletTradeList::where(function($query) use ($request){
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
            $query->process_status_val = $this->ProcessStatus[$query->process_status];
            $query->timestamp = date('Y-m-d H:i:s', floor($query->timestamp / 1000));
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //禁止充值
    public function stoporder(Request $request)
    {   
        $data = FmsWalletTradeList::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if(!in_array($data->process_status,[1,4,7,3])){
            return $this->responseData(400, '该状态不能禁止');
        }
        
        DB::beginTransaction();
        try {
            $data->process_status = 2;
            $data->tg_notice_status_send = 'Y';
            $data->process_comments = '人工禁止';
            $data->save();
            DB::commit();
            return $this->responseData(200, '禁止成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '禁止失败'.$e->getMessage());
        }
    }

}
