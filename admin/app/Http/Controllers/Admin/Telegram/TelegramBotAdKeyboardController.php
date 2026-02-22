<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotAd;
use App\Models\Telegram\TelegramBotKeyboard;
use App\Models\Telegram\TelegramBotAdKeyboard;
use Telegram\Bot\Api;

class TelegramBotAdKeyboardController extends Controller
{
    public $KeyboardType = ['1' => '键盘','2' => '内联按钮'];
    public $InlineType = ['0' => '-','1' => 'url','2' => '回调'];
    
    public function index(Request $request)
    {
        $BotAdData = TelegramBotAd::pluck('rid', 'rid');
        $KeyboardType = $this->KeyboardType;
        $keyboardData = TelegramBotKeyboard::select(DB::raw("CONCAT('ID:',rid,'-',keyboard_name) AS keyboard_name"),'rid','keyboard_type')->pluck('keyboard_name','rid','keyboard_type'); 
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.telegram.telegrambotadkeyboard.index',compact("BotAdData","keyboardData","KeyboardType","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotAdKeyboard::from('t_telegram_bot_ad_keyboard as a')
                ->join('t_telegram_bot as b','a.bot_rid','b.rid')
                ->join('t_telegram_bot_ad as c','a.ad_rid','c.rid')
                ->join('t_telegram_bot_keyboard as d','a.keyboard_rid','d.rid')
                ->where(function($query) use ($request){
                if ($request->notice_ad != '') {
                    $query->where('c.notice_ad', 'like' ,"%" . $request->notice_ad ."%");
                }     
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                } 
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username','d.keyboard_name','d.keyboard_type','d.inline_type')->orderBy('a.rid','desc')->get();
        
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
        $data = TelegramBotAdKeyboard::where('ad_rid', $request->ad_rid)->where('keyboard_rid', $request->keyboard_rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '定时广告键盘已存在');
        }
        
        //同一个广告只能添加一种类型
        $keyboard = TelegramBotKeyboard::where('rid',$request->keyboard_rid)->value('keyboard_type');
        
        $adkeyboard = TelegramBotAdKeyboard::from('t_telegram_bot_ad_keyboard as a')
                    ->join('t_telegram_bot_keyboard as b','a.keyboard_rid','b.rid')
                    ->where('b.keyboard_type','<>',$keyboard)
                    ->where('a.ad_rid', $request->ad_rid)
                    ->first();
        
        if($adkeyboard){
            return $this->responseData(400, '同一个定时广告,只能关联一种键盘类型');
        }
        
        $bot_rid = TelegramBotAd::where('rid',$request->ad_rid)->first();
        
        $res = TelegramBotAdKeyboard::create([
            'bot_rid' => $bot_rid->bot_rid,
            'ad_rid' => $request->ad_rid,
            'keyboard_rid' => $request->keyboard_rid,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotAdKeyboard::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TelegramBotAdKeyboard::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        $existdata = TelegramBotAdKeyboard::where('ad_rid', $request->ad_rid)->where('keyboard_rid', $request->keyboard_rid)->where('rid','<>',$request->rid)->first();
        if(!empty($existdata)){
            return $this->responseData(400, '定时广告键盘已存在');
        }
        
        //同一个广告只能添加一种类型
        $keyboard = TelegramBotKeyboard::where('rid',$request->keyboard_rid)->value('keyboard_type');
        
        $adkeyboard = TelegramBotAdKeyboard::from('t_telegram_bot_ad_keyboard as a')
                    ->join('t_telegram_bot_keyboard as b','a.keyboard_rid','b.rid')
                    ->where('b.keyboard_type','<>',$keyboard)
                    ->where('a.ad_rid', $request->ad_rid)
                    ->first();
        
        if($adkeyboard){
            return $this->responseData(400, '同一个定时广告,只能关联一种键盘类型');
        }
            
        DB::beginTransaction();
        try {
            $bot_rid = TelegramBotAd::where('rid',$request->ad_rid)->first();
            
            $data->bot_rid = $bot_rid->bot_rid;
            $data->ad_rid = $request->ad_rid;
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
    
    //快捷添加
    public function fastadd(Request $request)
    {
        if(empty($request->ad_rid) || empty($request->keyboard_type)){
            return $this->responseData(400, '参数必填');
        }
        
        $data = TelegramBotAd::where('rid', $request->ad_rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '广告不存在');
        }
        
        $boardData = TelegramBotKeyboard::where('keyboard_type', $request->keyboard_type)->get();
        
        if($boardData->count() == 0){
            return $this->responseData(400, '键盘类型无键盘数据');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotAdKeyboard::where('ad_rid', $request->ad_rid)->delete();
            
            TelegramBotAdKeyboard::insertUsing([
                'bot_rid', 'ad_rid', 'keyboard_rid', 'create_time'
            ], TelegramBotKeyboard::selectRaw(
                "$data->bot_rid, $request->ad_rid, rid, sysdate()"
            )->where('keyboard_type', $request->keyboard_type));
            
            DB::commit();
            return $this->responseData(200, '添加成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '添加失败'.$e->getMessage());
        }
        
    }
}
