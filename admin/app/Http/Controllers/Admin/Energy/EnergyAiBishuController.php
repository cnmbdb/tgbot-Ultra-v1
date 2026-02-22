<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyAiBishu;
use App\Models\Telegram\TelegramBot;

class EnergyAiBishuController extends Controller
{
    public $Status = ['0' => '开启','1' => '关闭','2' => '管理员关闭'];
    public $isBuy = ['Y' => '需要下单','N' => '无需下单','B' => '下单中'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        return view('admin.energy.aibishu.index',compact("Status","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyAiBishu::from('energy_ai_bishu as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->tg_uid != '') {
                    $query->where('tg_uid', 'like' ,"%" . $request->tg_uid ."%");
                }   
                if ($request->wallet_addr != '') {
                    $query->where('wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
                }  
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $data = $data->map(function($query){
            $query->status_val = $this->Status[$query->status];
            $query->is_buy_val = $this->isBuy[$query->is_buy];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    
    //编辑
    public function update(Request $request)
    {
        if(empty($request->wallet_addr) || empty($request->bot_rid)){
            return $this->responseData(400, '钱包地址不能为空');
        }
        
        $dataexist = EnergyAiBishu::where('wallet_addr',$request->wallet_addr)->where('rid','<>',$request->rid)->first();
        if(!empty($dataexist)){
            return $this->responseData(400, '钱包地址已存在');
        }
        
        $data = EnergyAiBishu::where('rid',$request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        if($request->bot_rid != $data->bot_rid || $request->wallet_addr != $data->wallet_addr || $request->max_buy_quantity != $data->max_buy_quantity){
            llog($data->rid."的原地址为：".$data->wallet_addr."。新地址为：".$request->wallet_addr."。原机器人ID：".$data->bot_rid."。新机器人ID：".$request->bot_rid."。原笔数：".$data->max_buy_quantity."。新笔数：".$request->max_buy_quantity,"modify");
        }
        
        DB::beginTransaction();  
        try {
            $data->bot_rid = $request->bot_rid;
            $data->status = $request->status;
            $data->wallet_addr = $request->wallet_addr;
            $data->tg_uid = $request->tg_uid;
            $data->total_buy_quantity = $request->total_buy_quantity ?? 0;
            $data->back_comments = $request->back_comments;
            $data->max_buy_quantity = $request->max_buy_quantity ?? 0;
            $data->bishu_stop_day = $request->bishu_stop_day ?? 0;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyAiBishu::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
    
    //刷新
    public function refresh(Request $request)
    {
        $res = EnergyAiBishu::where('rid', $request->rid)->update(['is_buy' => 'N']);
        return $res ? $this->responseData(200, '刷新成功,请等待更新') : $this->responseData(400, '刷新失败');
    }
    
    //添加
    public function add(Request $request)
    {
        if(empty($request->wallet_addr) || strlen($request->wallet_addr) != 34){
            return $this->responseData(400, '输入有效的波场地址');
        }
        
        if($request->total_buy_usdt < 0){
            return $this->responseData(400, '总购买USDT不能小于0');
        }
        
        if($request->max_buy_quantity < 0){
            return $this->responseData(400, '最大购买次数不能小于0');
        }
        
        if($request->total_buy_quantity < 0){
            return $this->responseData(400, '已购买次数不能小于0');
        }
        
        if(empty($request->bot_rid)){
            return $this->responseData(400, '请选择机器人');
        }
        
        $dataexist = EnergyAiBishu::where('wallet_addr',$request->wallet_addr)->first();
        if(!empty($dataexist)){
            return $this->responseData(400, '钱包地址已存在');
        }
        
        $res = EnergyAiBishu::create([
            'bot_rid' => $request->bot_rid,
            'wallet_addr' => $request->wallet_addr,
            'tg_uid' => $request->tg_uid ?? '',
            'total_buy_usdt' => $request->total_buy_usdt ?? 0,
            'max_buy_quantity' => $request->max_buy_quantity ?? 0,
            'total_buy_quantity' => $request->total_buy_quantity ?? 0,
            'bishu_stop_day' => $request->bishu_stop_day ?? 0,
            'back_comments' => $request->back_comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }

}
