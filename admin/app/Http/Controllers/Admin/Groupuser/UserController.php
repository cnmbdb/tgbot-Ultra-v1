<?php

namespace App\Http\Controllers\Admin\Groupuser;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotUser;
use Telegram\Bot\Api;

class UserController extends Controller
{
    public $status = ['1' => '使用中','2' => '已停用','3' => '用户不存在'];
    
    public function index(Request $request)
    {
        $botData = TelegramBot::pluck('bot_username','rid'); 
        return view('admin.groupuser.user.index',compact("botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotUser::from('telegram_bot_user as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->tg_username != '') {
                    $query->where('a.tg_username', 'like' ,"%" . str_replace(['https://t.me/','@'],'',$request->tg_username) ."%");
                }   
                if ($request->tg_uid != '') {
                    $query->where('a.tg_uid', 'like' ,"%" . $request->tg_uid ."%");
                } 
                if ($request->tg_nickname != '') {
                    $query->where('a.tg_nickname', 'like' ,"%" . $request->tg_nickname ."%");
                } 
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $status = $this->status;
        
        $data = $data->map(function($query) use ($status){
            $query->status_val = $status[$query->status];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //发送消息
    public function sendmessage(Request $request)
    {
        if(empty($request->message) || $request->message == ''){
            return $this->responseData(400, '消息内容不能为空');
        }
        
        $data = TelegramBotUser::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        if($data->status <> 1){
            return $this->responseData(400, '用户状态不为使用中');
        }
        
        $botdata = TelegramBot::where('rid', $data->bot_rid)->first();
        if(empty($botdata)){
            return $this->responseData(400, '机器人数据不存在');
        }
        
        //发送消息
        $sendmessageurl = 'https://api.telegram.org/bot'.$botdata->bot_token.'/sendMessage?chat_id='.$data->tg_uid.'&text='.urlencode($request->message).'&parse_mode=HTML';
        
        $res = Get_Curl($sendmessageurl);

        if(empty($res)){
            return $this->responseData(400, '发送失败,检查服务器是否可以访问外网');
        }
        $res = json_decode($res,true);

        if($res['ok'] && $res['result']){
            return $this->responseData(200, '发送成功');
        }
        
        if($res['description'] == 'Forbidden: user is deactivated'){
            DB::beginTransaction();
            try {
                $data->status = 3;
                $data->stop_time = nowDate();
                $data->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
            
            return $this->responseData(400, '发送失败:'.$res['description']);
        }
        
        return $this->responseData(400, '发送失败:'.$res['description']);
    }
    
    //批量发送消息
    public function batchsendmessage(Request $request)
    {
        if(empty($request->message) || $request->message == ''){
            return $this->responseData(400, '消息内容不能为空');
        }
        
        $botdata = TelegramBot::where('rid', $request->bot_rid)->first();
        if(empty($botdata)){
            return $this->responseData(400, '机器人数据不存在');
        }
        
        $data = TelegramBotUser::where('bot_rid', $request->bot_rid)->where('status',1)->get();
        if($data->count() == 0){
            return $this->responseData(400, '机器人无用户数据');
        }
        
        foreach ($data as $k => $v) {
            //发送消息
            $sendmessageurl = 'https://api.telegram.org/bot'.$botdata->bot_token.'/sendMessage?chat_id='.$v->tg_uid.'&text='.urlencode($request->message).'&parse_mode=HTML';
            
            $res = Get_Curl($sendmessageurl);
            
            if(empty($res)){
                continue;
            }
            $res = json_decode($res,true);
    
            if($res['ok'] && $res['result']){
                continue;
            }
            
            if($res['description'] == 'Forbidden: user is deactivated'){
                DB::beginTransaction();
                try {
                    $data = TelegramBotUser::where('rid', $v->rid)->first();
                    $save_data = [];
                    $save_data['status'] = 3;
                    $save_data['stop_time'] = nowDate();
                    TelegramBotUser::where('rid',$v->rid)->update($save_data);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                continue;
            }
        }
        
        return $this->responseData(200, '发送完成');
    }
    
    //人工充值
    public function rechargemanual(Request $request)
    {   
        $data = TelegramBotUser::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '用户不存在');
        }
        
        DB::beginTransaction();
        try {
            $data->cash_trx = $request->cash_trx;
            $data->cash_usdt = $request->cash_usdt;
            $data->max_monitor_wallet = $request->max_monitor_wallet;
            $data->save();
            DB::commit();
            return $this->responseData(200, '充值成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '充值失败'.$e->getMessage());
        }
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotUser::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
    
    //批量删除
    public function batchdelete(Request $request)
    {
        $res = TelegramBotUser::where('bot_rid', $request->bot_rid)->whereIn('status',[2,3])->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
}
