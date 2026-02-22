<?php

namespace App\Http\Controllers\Admin\Monitor;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Monitor\MonitorWallet;

class MonitorWalletController extends Controller
{
    public $ChainType = ['trc' => 'trc'];
    public $MonitorWalletStatus = ['开启','关闭'];
    public $MonitorTransaction = ['YY' => '开启','NN' => '关闭'];
    
    public function index(Request $request)
    {
        $ChainType = $this->ChainType;
        $MonitorWalletStatus = $this->MonitorWalletStatus;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.monitor.wallet.index',compact("ChainType","MonitorWalletStatus","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = MonitorWallet::from('monitor_wallet as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->monitor_wallet != '') {
                    $query->where('a.monitor_wallet', 'like' ,"%" . $request->monitor_wallet ."%");
                }   
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $keys = ["MonitorTransaction" => $this->MonitorTransaction];
        
        $data = $data->map(function($query) use ($keys){
            $query->monitor_usdt_transaction_val = $keys["MonitorTransaction"][$query->monitor_usdt_transaction];
            $query->monitor_trx_transaction_val = $keys["MonitorTransaction"][$query->monitor_trx_transaction];
            $query->monitor_approve_transaction_val = $keys["MonitorTransaction"][$query->monitor_approve_transaction];
            $query->monitor_multi_transaction_val = $keys["MonitorTransaction"][$query->monitor_multi_transaction];
            $query->monitor_pledge_transaction_val = $keys["MonitorTransaction"][$query->monitor_pledge_transaction];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = MonitorWallet::where('monitor_wallet', $request->monitor_wallet)->where('chain_type', $request->chain_type)->first();
        if(!empty($data)){
            return $this->responseData(400, '监控钱包已存在');
        }
        
        if(empty($request->chain_type) || empty($request->monitor_wallet)){
            return $this->responseData(400, '有必填项未填写,请检查');
        }
        
        $res = MonitorWallet::create([
            'bot_rid' => $request->bot_rid,
            'chain_type' => $request->chain_type,
            'monitor_wallet' => $request->monitor_wallet,
            'tg_notice_obj' => $request->tg_notice_obj,
            'balance_alert' => $request->balance_alert ?? 0,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //批量添加
    public function batchadd(Request $request)
    {
        $explodeArr = explode("\n", $request->monitor_wallet);
        if(count($explodeArr) > 0){
            
            for ($i = 0; $i < count($explodeArr); $i++) {
                $monitor = explode(',',$explodeArr[$i]);
                $monitorWallet = str_replace("\n", "", $monitor[0]);
                $monitorComment = str_replace("\n", "", isset($monitor[1]) ?$monitor[1]:'');
                $data = MonitorWallet::where('monitor_wallet', $request->monitor_wallet)->where('chain_type', $request->chain_type)->first();
                if(!empty($data)){
                    continue;
                }
                
                $res = MonitorWallet::create([
                    'bot_rid' => $request->bot_rid,
                    'chain_type' => $request->chain_type,
                    'monitor_wallet' => $monitorWallet,
                    'tg_notice_obj' => $request->tg_notice_obj,
                    'balance_alert' => $request->balance_alert ?? 0,
                    'comments' => $monitorComment ?? '',
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
        $res = MonitorWallet::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = MonitorWallet::where('monitor_wallet', $request->monitor_wallet)->where('chain_type', $request->chain_type)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '监控钱包已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = MonitorWallet::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->chain_type = $request->chain_type;
            $data->monitor_wallet = $request->monitor_wallet;
            $data->tg_notice_obj = $request->tg_notice_obj;
            $data->balance_alert = $request->balance_alert ?? 0;
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
            $data = MonitorWallet::where('rid', $request->rid)->first();
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
