<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotKeyreply;
use App\Models\Telegram\TelegramBotKeyreplyKeyboard;
use Telegram\Bot\Api;
use App\Http\Controllers\Admin\Setting\ConfigController;

class KeyreplyController extends Controller
{
    
    public $KeyreplyStatus = ['启用','禁用'];
    public $KeyreplyKeytype = ["1" => "消息内容", "2" => "入群通知"];
    public $KeyreplyOpttype = ["1" => "回复消息(通用)", "2" => "回复ID", "3" => "回复消息(通用)+能量按钮(闪租套餐)", "4" => "回复消息(私聊)+会员按钮", "5" => "回复消息(私聊)+充值按钮", "6" => "回复消息(私聊)+监控按钮", "7" => "回复消息(私聊)+商品按钮", "8" => "回复消息(私聊)+个人中心", "9" => "回复消息(通用)+欧意汇率", "10" => "回复消息(通用)+能量按钮(笔数套餐)", "11" => "回复消息(通用)+能量按钮(智能托管)"];
    
    public function index(Request $request)
    {
        $KeyreplyStatus = $this->KeyreplyStatus;
        $KeyreplyKeytype = $this->KeyreplyKeytype;
        $KeyreplyOpttype = $this->KeyreplyOpttype;
        
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.telegram.keyreply.index',compact("KeyreplyStatus","KeyreplyKeytype","KeyreplyOpttype","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotKeyreply::from('telegram_bot_keyreply as a')
                ->join('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->monitor_word != '') {
                    $query->where('a.monitor_word', 'like' ,"%" . $request->monitor_word ."%");
                } 
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }     
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $keys = ["Keytype" => $this->KeyreplyKeytype, "Opttype" => $this->KeyreplyOpttype];
        
        $data = $data->map(function($query) use ($keys){
            $query->key_type_val = $keys["Keytype"][$query->key_type];
            $query->opt_type_val = $keys["Opttype"][$query->opt_type];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TelegramBotKeyreply::where('bot_rid', $request->bot_rid)->where('monitor_word', $request->monitor_word)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人回复关键字已存在');
        }
        
        $res = TelegramBotKeyreply::create([
            'bot_rid' => $request->bot_rid,
            'key_type' => $request->key_type,
            'monitor_word' => $request->monitor_word,
            'reply_photo' => '',
            'reply_content' => $request->reply_content ?? '--',
            'opt_type' => $request->opt_type,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $data = TelegramBotKeyreplyKeyboard::where('keyreply_rid', $request->rid);
        if($data->count() > 0){
            return $this->responseData(400, '请先删除关键字键盘');
        }
        
        $res = TelegramBotKeyreply::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request, ConfigController $upload)
    {
        $data = TelegramBotKeyreply::where('bot_rid', $request->bot_rid)->where('monitor_word', $request->monitor_word)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人回复关键字已存在');
        }
        
        $data = TelegramBotKeyreply::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        if(!empty($request->file('thumb'))){
            $filedata = $upload->uploadfile($request->file('thumb'), 'news');
            $fileurl = $filedata['data']['url'];
        }else{
            $fileurl = $data->reply_photo;
        }
        
        DB::beginTransaction();
        try {
            $data->monitor_word = $request->monitor_word;
            $data->reply_photo = $fileurl;
            $data->reply_content = $request->reply_content ?? '--';
            $data->key_type = $request->key_type;
            $data->opt_type = $request->opt_type;
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
            $data = TelegramBotKeyreply::where('rid', $request->rid)->first();
            $data->status = $request->status == 1 ? 0 : 1;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    // 编辑页面查看
    public function show(Request $request)
    {
        $KeyreplyStatus = $this->KeyreplyStatus;
        $KeyreplyKeytype = $this->KeyreplyKeytype;
        $KeyreplyOpttype = $this->KeyreplyOpttype;
        
        $botData = TelegramBot::pluck('bot_token','rid'); 
        
        $data = TelegramBotKeyreply::from('telegram_bot_keyreply as a')
            ->join('telegram_bot as b','a.bot_rid','b.rid')
            ->where('a.rid',$request->rid)
            ->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')
            ->first();
            
        return view('admin.telegram.keyreply.edit',compact("KeyreplyStatus","KeyreplyKeytype","KeyreplyOpttype","botData","data"));
        
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
        
        $data = TelegramBotKeyreply::where('bot_rid', $request->copy_bot_rid)->get();
        
        if($data->count() == 0){
            return $this->responseData(400, '来源机器人无数据可复制');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotKeyreply::where('bot_rid', $request->paste_bot_rid)->delete();
            TelegramBotKeyreplyKeyboard::where('bot_rid', $request->paste_bot_rid)->delete();
            
            TelegramBotKeyreply::insertUsing([
                'bot_rid', 'key_type', 'monitor_word', 'reply_photo','reply_content','opt_type','status','create_time'
            ], TelegramBotKeyreply::selectRaw(
                "$request->paste_bot_rid, key_type, monitor_word, reply_photo, reply_content, opt_type, status, sysdate()"
            )->where('bot_rid', $request->copy_bot_rid));
            
            DB::commit();
            return $this->responseData(200, '复制成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败'.$e->getMessage());
        }
        
    }
}
