<?php

namespace App\Http\Controllers\Admin\Monitor;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Monitor\MonitorBot;

class MonitorBotController extends Controller
{
    public $MonitorWalletStatus = ['开启','关闭'];
    
    public function index(Request $request)
    {
        $MonitorWalletStatus = $this->MonitorWalletStatus;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.monitor.bot.index',compact("MonitorWalletStatus","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = MonitorBot::from('t_monitor_bot as a')
                 ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->monitor_wallet != '') {
                    $query->where('a.monitor_wallet', 'like' ,"%" . $request->monitor_wallet ."%");
                }   
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = MonitorBot::where('bot_rid', $request->bot_rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人监控已存在');
        }
        
        $res = MonitorBot::create([
            'bot_rid' => $request->bot_rid,
            'price_usdt_5' => $request->price_usdt_5 ?? 0,
            'price_usdt_10' => $request->price_usdt_10 ?? 0,
            'price_usdt_20' => $request->price_usdt_20 ?? 0,
            'price_usdt_50' => $request->price_usdt_50 ?? 0,
            'price_usdt_100' => $request->price_usdt_100 ?? 0,
            'price_usdt_200' => $request->price_usdt_200 ?? 0,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = MonitorBot::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = MonitorBot::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->price_usdt_5 = $request->price_usdt_5 ?? 0;
            $data->price_usdt_10 = $request->price_usdt_10 ?? 0;
            $data->price_usdt_20 = $request->price_usdt_20 ?? 0;
            $data->price_usdt_50 = $request->price_usdt_50 ?? 0;
            $data->price_usdt_100 = $request->price_usdt_100 ?? 0;
            $data->price_usdt_200 = $request->price_usdt_200 ?? 0;
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
            $data = MonitorBot::where('rid', $request->rid)->first();
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
