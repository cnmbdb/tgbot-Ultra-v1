<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotKeyreply;
use App\Models\Telegram\TelegramBotKeyboard;
use App\Models\Telegram\TelegramBotKeyreplyKeyboard;
use Telegram\Bot\Api;

class KeyreplyboardController extends Controller
{
    public $KeyboardType = ['1' => '键盘','2' => '内联按钮'];
    public $InlineType = ['0' => '-','1' => 'url','2' => '回调'];
    
    public function index(Request $request)
    {
        $keyreplyData = TelegramBotKeyreply::select(DB::raw("CONCAT('ID:',rid,'-',monitor_word) AS monitor_word"),'rid')->pluck('monitor_word', 'rid');
        $KeyboardType = $this->KeyboardType;
        $keyboardData = TelegramBotKeyboard::select(DB::raw("CONCAT('ID:',rid,'-',keyboard_name) AS keyboard_name"),'rid','keyboard_type')->pluck('keyboard_name','rid','keyboard_type'); 
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.telegram.keyreplyboard.index',compact("keyreplyData","keyboardData","KeyboardType","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotKeyreplyKeyboard::from('telegram_bot_keyreply_keyboard as a')
                ->join('telegram_bot as b','a.bot_rid','b.rid')
                ->join('telegram_bot_keyreply as c','a.keyreply_rid','c.rid')
                ->join('telegram_bot_keyboard as d','a.keyboard_rid','d.rid')
                ->where(function($query) use ($request){
                if ($request->monitor_word != '') {
                    $query->where('c.monitor_word', 'like' ,"%" . $request->monitor_word ."%");
                }      
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username','c.monitor_word','d.keyboard_name','d.keyboard_type','d.inline_type')->orderBy('a.rid','desc')->get();
        
        $keys = ["KeyboardType" => $this->KeyboardType, "InlineType" => $this->InlineType];
        
        $data = $data->map(function($query) use ($keys){
            $query->keyboard_type_val = $keys["KeyboardType"][$query->keyboard_type];
            $query->inline_type_val = $keys["InlineType"][$query->inline_type];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TelegramBotKeyreplyKeyboard::where('keyreply_rid', $request->keyreply_rid)->where('keyboard_rid', $request->keyboard_rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人关键字键盘已存在');
        }
        
        $bot_rid = TelegramBotKeyreply::where('rid',$request->keyreply_rid)->first();
        
        $res = TelegramBotKeyreplyKeyboard::create([
            'bot_rid' => $bot_rid->bot_rid,
            'keyreply_rid' => $request->keyreply_rid,
            'keyboard_rid' => $request->keyboard_rid,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotKeyreplyKeyboard::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TelegramBotKeyreplyKeyboard::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        $existdata = TelegramBotKeyreplyKeyboard::where('keyreply_rid', $request->keyreply_rid)->where('keyboard_rid', $request->keyboard_rid)->where('rid','<>',$request->keyreply_rid)->first();
        if(!empty($existdata)){
            return $this->responseData(400, '机器人关键字键盘已存在');
        }
            
        DB::beginTransaction();
        try {
            $bot_rid = TelegramBotKeyreply::where('rid',$request->keyreply_rid)->first();
            
            $data->bot_rid = $bot_rid->bot_rid;
            $data->keyreply_rid = $request->keyreply_rid;
            $data->keyboard_rid = $request->keyboard_rid;
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //快捷添加-按关键字
    public function fastadd(Request $request)
    {
        if(empty($request->keyreply_rid) || empty($request->keyboard_type)){
            return $this->responseData(400, '参数必填');
        }
        
        $data = TelegramBotKeyreply::where('rid', $request->keyreply_rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '关键字不存在');
        }
        
        $boardData = TelegramBotKeyboard::where('keyboard_type', $request->keyboard_type)->get();
        
        if($boardData->count() == 0){
            return $this->responseData(400, '键盘类型无键盘数据');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotKeyreplyKeyboard::where('keyreply_rid', $request->keyreply_rid)->delete();
            
            TelegramBotKeyreplyKeyboard::insertUsing([
                'bot_rid', 'keyreply_rid', 'keyboard_rid', 'create_time'
            ], TelegramBotKeyboard::selectRaw(
                "$data->bot_rid, $request->keyreply_rid, rid, sysdate()"
            )->where('keyboard_type', $request->keyboard_type));
            
            DB::commit();
            return $this->responseData(200, '添加成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '添加失败'.$e->getMessage());
        }
        
    }
    
    //快捷添加-按机器人
    public function fastbotadd(Request $request)
    {
        if(empty($request->bot_rid) || empty($request->keyboard_type)){
            return $this->responseData(400, '参数必填');
        }
        
        $data = TelegramBot::where('rid', $request->bot_rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '机器人不存在');
        }
        
        $boardData = TelegramBotKeyboard::where('keyboard_type', $request->keyboard_type)->get();
        
        if($boardData->count() == 0){
            return $this->responseData(400, '键盘类型无键盘数据');
        }
        
        $replydata = TelegramBotKeyreply::where('bot_rid', $request->bot_rid)->get();
        
        if($replydata->count() == 0){
            return $this->responseData(400, '机器人无关键字');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotKeyreplyKeyboard::where('bot_rid', $request->bot_rid)->delete();
            
            foreach ($replydata as $k => $v) {
                $keyreply_rid = $v['rid'];
                TelegramBotKeyreplyKeyboard::insertUsing([
                    'bot_rid', 'keyreply_rid', 'keyboard_rid', 'create_time'
                ], TelegramBotKeyboard::selectRaw(
                    "$request->bot_rid, $keyreply_rid, rid, sysdate()"
                )->where('keyboard_type', $request->keyboard_type));
            
            }
            DB::commit();
            return $this->responseData(200, '添加成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '添加失败'.$e->getMessage());
        }
        
    }
    
    //按机器人键盘删除
    public function fastbotdelete(Request $request)
    {
        if(empty($request->bot_rid) || empty($request->keyboard_rid)){
            return $this->responseData(400, '参数必填');
        }
        
        $data = TelegramBot::where('rid', $request->bot_rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '机器人不存在');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotKeyreplyKeyboard::where('bot_rid', $request->bot_rid)->where("keyboard_rid",$request->keyboard_rid)->delete();
            
            DB::commit();
            return $this->responseData(200, '删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '删除失败'.$e->getMessage());
        }
        
    }
}
