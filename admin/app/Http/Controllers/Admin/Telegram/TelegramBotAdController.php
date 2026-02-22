<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotAd;
use App\Models\Telegram\TelegramBotAdKeyboard;
use Telegram\Bot\Api;
use App\Http\Controllers\Admin\Setting\ConfigController;

class TelegramBotAdController extends Controller
{
    
    public $TelegramBotAdStatus = ['启用','禁用'];
    public $NoticeCycle = ["1" => "每分钟", "2" => "每10分钟", "3" => "每30分钟", "4" => "每小时", "6" => "每3小时", "7" => "每6小时", "8" => "每12小时", "5" => "每天(24小时)", "9" => "每2天", "10" => "每5天", "11" => "每10天", "12" => "每15天", "13" => "每20天", "14" => "每30天", "15" => "每60天", "16" => "每90天", "17" => "每180天"];
    
    public function index(Request $request)
    {
        $TelegramBotAdStatus = $this->TelegramBotAdStatus;
        $NoticeCycle = $this->NoticeCycle;
        
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.telegram.telegrambotad.index',compact("TelegramBotAdStatus","NoticeCycle","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotAd::from('telegram_bot_ad as a')
                ->join('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->notice_ad != '') {
                    $query->where('a.notice_ad', 'like' ,"%" . $request->notice_ad ."%");
                }      
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $keys = ["noticecycle" => $this->NoticeCycle];
        
        $data = $data->map(function($query) use ($keys){
            $query->notice_cycle_val = $keys["noticecycle"][$query->notice_cycle];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TelegramBotAd::where('bot_rid', $request->bot_rid)->where('notice_ad', $request->notice_ad)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人定时广告已存在');
        }
        
        $res = TelegramBotAd::create([
            'bot_rid' => $request->bot_rid,
            'notice_cycle' => $request->notice_cycle,
            'notice_obj' => $request->notice_obj,
            'notice_photo' => '',
            'notice_ad' => $request->notice_ad,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = TelegramBotAd::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request, ConfigController $upload)
    {
        $data = TelegramBotAd::where('bot_rid', $request->bot_rid)->where('notice_ad', $request->notice_ad)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人定时广告已存在');
        }
        
        $data = TelegramBotAd::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        if(!empty($request->file('thumb'))){
            $filedata = $upload->uploadfile($request->file('thumb'), 'news');
            $fileurl = $filedata['data']['url'];
        }else{
            $fileurl = $data->notice_photo;
        }
            
        DB::beginTransaction();
        try {
            $data->notice_cycle = $request->notice_cycle;
            $data->notice_obj = $request->notice_obj;
            $data->notice_photo = $fileurl;
            $data->notice_ad = $request->notice_ad;
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
            $data = TelegramBotAd::where('rid', $request->rid)->first();
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
        $TelegramBotAdStatus = $this->TelegramBotAdStatus;
        $NoticeCycle = $this->NoticeCycle;
        
        $botData = TelegramBot::pluck('bot_token','rid'); 
        
        $data = TelegramBotAd::from('telegram_bot_ad as a')
            ->join('telegram_bot as b','a.bot_rid','b.rid')
            ->where('a.rid',$request->rid)
            ->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')
            ->first();
            
        return view('admin.telegram.telegrambotad.edit',compact("TelegramBotAdStatus","NoticeCycle","botData","data"));
        
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
        
        $data = TelegramBotAd::where('bot_rid', $request->copy_bot_rid)->get();
        
        if($data->count() == 0){
            return $this->responseData(400, '来源机器人无数据可复制');
        }
        
        DB::beginTransaction();
        try {
            TelegramBotAd::where('bot_rid', $request->paste_bot_rid)->delete();
            TelegramBotAdKeyboard::where('bot_rid', $request->paste_bot_rid)->delete();
            
            TelegramBotAd::insertUsing([
                'bot_rid', 'notice_cycle', 'notice_obj', 'notice_photo', 'notice_ad','status','create_time'
            ], TelegramBotAd::selectRaw(
                "$request->paste_bot_rid, notice_cycle, '-', notice_photo, notice_ad, status, sysdate()"
            )->where('bot_rid', $request->copy_bot_rid));
            
            DB::commit();
            return $this->responseData(200, '复制成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败'.$e->getMessage());
        }
        
    }
}
