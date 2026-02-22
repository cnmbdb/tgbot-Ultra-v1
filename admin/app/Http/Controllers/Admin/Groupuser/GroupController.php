<?php

namespace App\Http\Controllers\Admin\Groupuser;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotGroup;
use Telegram\Bot\Api;

class GroupController extends Controller
{
    public $status = ['1' => '使用中','2' => '已停用','3' => '群组不存在'];
    public $isadmin = ['Y' => '管理员','N' => '普通'];
    
    public function index(Request $request)
    {
        $botData = TelegramBot::pluck('bot_username','rid'); 
        return view('admin.groupuser.group.index',compact("botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotGroup::from('t_telegram_bot_group as a')
                ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->tg_groupusername != '') {
                    $query->where('a.tg_groupusername', 'like' ,"%" . str_replace(['https://t.me/','@'],'',$request->tg_groupusername) ."%");
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
        $isadmin = $this->isadmin;
        
        $data = $data->map(function($query) use ($status,$isadmin){
            $query->status_val = $status[$query->status];
            $query->is_admin_val = $isadmin[$query->is_admin];
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
        
        $data = TelegramBotGroup::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        if($data->status <> 1){
            return $this->responseData(400, '群组状态不为使用中');
        }
        
        $botdata = TelegramBot::where('rid', $data->bot_rid)->first();
        if(empty($botdata)){
            return $this->responseData(400, '机器人数据不存在');
        }
        
        //发送消息
        $sendmessageurl = 'https://api.telegram.org/bot'.$botdata->bot_token.'/sendMessage?chat_id='.$data->tg_groupid.'&text='.urlencode($request->message).'&parse_mode=HTML';
        
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
        }elseif($res['description'] == 'Forbidden: bot was kicked from the supergroup chat'){
            DB::beginTransaction();
            try {
                $data->status = 2;
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
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotGroup::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
}
