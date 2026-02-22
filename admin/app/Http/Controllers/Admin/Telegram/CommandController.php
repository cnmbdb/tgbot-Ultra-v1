<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotCommand;
use Telegram\Bot\Api;

class CommandController extends Controller
{
    public $CommandType = ["1" => "通用", "2" => "私聊", "3" => "群聊"];
    
    public function index(Request $request)
    {
        $CommandType = $this->CommandType;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.telegram.command.index',compact("CommandType","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotCommand::from('t_telegram_bot_command as a')
                ->join('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->command != '') {
                    $query->where('a.command', 'like' ,"%" . $request->command ."%");
                }
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','a.bot_rid','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $keys = ["CommandType" => $this->CommandType];
        
        $data = $data->map(function($query) use ($keys){
            $query->command_type_val = $keys["CommandType"][$query->command_type];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TelegramBotCommand::where('bot_rid', $request->bot_rid)->where('command', $request->command)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人命令已存在');
        }
        
        $res = TelegramBotCommand::create([
            'bot_rid' => $request->bot_rid,
            'command' => $request->command,
            'description' => $request->description,
            'command_type' => $request->command_type ?? 1,
            'seq_sn' => $request->seq_sn ?? 0,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotCommand::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {   
        $data = TelegramBotCommand::where('bot_rid', $request->bot_rid)->where('command', $request->command)->where('rid','<>',$request->rid)->first();
        
        if(!empty($data)){
            return $this->responseData(400, '机器人命令已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = TelegramBotCommand::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->command = $request->command;
            $data->description = $request->description;
            $data->command_type = $request->command_type;
            $data->seq_sn = $request->seq_sn;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }
    
    //同步
    public function sync(Request $request)
    {
        $data = TelegramBot::where('rid', $request->bot_rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        $commandData = TelegramBotCommand::where('bot_rid', $request->bot_rid)->get();
        
        $commandsone = [];
        $commandstwo = [];
        if($commandData->count() > 0){
            $sone = 0;
            $stwo = 0;
            foreach ($commandData as $k => $v) {
                //私聊
                if($v->command_type == 1 || $v->command_type == 2){
                    $commandone = [];
                    //内联按钮
                    $commandone['command'] = $v->command;
                    $commandone['description'] = $v->description;
                    
                    $commandsone[$sone] = $commandone;
                    $sone++;
                }
                //群聊
                if($v->command_type == 1 || $v->command_type == 3){
                    $commandtwo = [];
                    //内联按钮
                    $commandtwo['command'] = $v->command;
                    $commandtwo['description'] = $v->description;
                    
                    $commandstwo[$stwo] = $commandtwo;
                    $stwo++;
                }
            }
        }
        
        //私聊
        $encodedCommandsone = json_encode($commandsone); 
        $sendmessageurl = "https://api.telegram.org/bot". $data->bot_token ."/setMyCommands?commands=".$encodedCommandsone."&scope=".'{"type":"all_private_chats"}';
        $res = Get_Curl($sendmessageurl);
        //群聊
        $encodedCommandstwo = json_encode($commandstwo); 
        $sendmessageurl = "https://api.telegram.org/bot". $data->bot_token ."/setMyCommands?commands=".$encodedCommandstwo."&scope=".'{"type":"all_group_chats"}';
        $res = Get_Curl($sendmessageurl);
        
        if(empty($res)){
            return $this->responseData(400, '同步失败,检查服务器是否可以访问外网');
        }
        $res = json_decode($res,true);

        if($res['ok'] && $res['result']){
            return $this->responseData(200, '同步成功');
        }
        
        return $this->responseData(400, '同步失败:'.$res['description']);
    }
    
    //复制
    public function copyPaste(Request $request)
    {   
        if(empty($request->copy_bot_rid) || empty($request->paste_bot_rid)){
            return $this->responseData(400, '覆盖和来源机器人必填');
        }
        
        if($request->copy_bot_rid == $request->paste_bot_rid){
            return $this->responseData(400, '覆盖和来源机器人不能一致');
        }
        
        $copyData = TelegramBot::where('rid', $request->copy_bot_rid)->first();
        
        if(empty($copyData)){
            return $this->responseData(400, '来源机器人不存在');
        }
        
        $pasteData = TelegramBot::where('rid', $request->paste_bot_rid)->first();
        
        if(empty($pasteData)){
            return $this->responseData(400, '覆盖机器人不存在');
        }
        
        $data = TelegramBotCommand::where('bot_rid', $request->copy_bot_rid)->get();
        
        if($data->count() == 0){
            return $this->responseData(400, '来源机器人无数据可复制');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotCommand::where('bot_rid', $request->paste_bot_rid)->delete();
            
            TelegramBotCommand::insertUsing([
                'bot_rid', 'command', 'description', 'command_type','seq_sn','create_time'
            ], TelegramBotCommand::selectRaw(
                "$request->paste_bot_rid, command, description, command_type, seq_sn, sysdate()"
            )->where('bot_rid', $request->copy_bot_rid));
            
            DB::commit();
            return $this->responseData(200, '复制成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败'.$e->getMessage());
        }
        
    }
}
