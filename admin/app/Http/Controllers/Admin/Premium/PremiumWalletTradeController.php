<?php

namespace App\Http\Controllers\Admin\Premium;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Premium\PremiumWalletTradeList;

class PremiumWalletTradeController extends Controller
{
    public $ProcessStatus = ['6' => '会员平台未启用','7' => '金额无对应订单','8' => '下单中','9' => '下单成功','1' => '待下单','5' => '会员平台未配置正确','4' => '下单失败','2' => '人工禁止'];
    
    public function index(Request $request)
    {
        return view('admin.premium.trade.index');
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = PremiumWalletTradeList::where(function($query) use ($request){
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
    public function reorder(Request $request)
    {   
        $data = PremiumWalletTradeList::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if(!in_array($data->process_status,[4,5,6,7])){
            return $this->responseData(400, '该状态禁止补发');
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
    public function stoporder(Request $request)
    {   
        $data = PremiumWalletTradeList::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '交易数据不存在');
        }
        
        if(!in_array($data->process_status,[1,4,5,6,7])){
            return $this->responseData(400, '该状态不能禁止');
        }
        
        DB::beginTransaction();
        try {
            $data->process_status = 2;
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
