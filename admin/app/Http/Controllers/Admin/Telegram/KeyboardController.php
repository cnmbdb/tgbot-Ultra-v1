<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotKeyboard;
use App\Models\Telegram\TelegramBotKeyreplyKeyboard;
use Telegram\Bot\Api;

class KeyboardController extends Controller
{
    public $KeyboardStatus = ['启用','禁用'];
    public $KeyboardType = ['1' => '键盘','2' => '内联按钮'];
    public $InlineType = ['0' => '-','1' => 'url','2' => '回调'];
    
    public function index(Request $request)
    {
        $KeyboardStatus = $this->KeyboardStatus;
        $KeyboardType = $this->KeyboardType;
        $InlineType = $this->InlineType;
        
        return view('admin.telegram.keyboard.index',compact("KeyboardStatus","KeyboardType","InlineType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBotKeyboard::where(function($query) use ($request){
            if ($request->keyboard_name != '') {
                $query->where('keyboard_name', 'like' ,"%" . $request->keyboard_name ."%");
            }      
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->orderBy('rid','desc')->get();
        
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
        $data = TelegramBotKeyboard::where('keyboard_name', $request->keyboard_name)->where('keyboard_type', $request->keyboard_type)->where('inline_type', $request->inline_type)->where('keyboard_value', $request->keyboard_value)->first();
        if(!empty($data)){
            return $this->responseData(400, '键盘名称已存在');
        }
        
        $res = TelegramBotKeyboard::create([
            'keyboard_type' => $request->keyboard_type,
            'keyboard_name' => $request->keyboard_name,
            'inline_type' => $request->inline_type,
            'keyboard_value' => $request->keyboard_value ?? '-',
            'seq_sn' => $request->seq_sn,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $data = TelegramBotKeyreplyKeyboard::where('keyboard_rid', $request->rid);
        if($data->count() > 0){
            return $this->responseData(400, '请先删除关键字键盘');
        }
        
        $res = TelegramBotKeyboard::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TelegramBotKeyboard::where('keyboard_name', $request->keyboard_name)->where('keyboard_type', $request->keyboard_type)->where('inline_type', $request->inline_type)->where('keyboard_value', $request->keyboard_value)->where('rid', '<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '键盘名称已存在');
        }
            
        DB::beginTransaction();
        try {
            $data = TelegramBotKeyboard::where('rid', $request->rid)->first();
            $data->keyboard_type = $request->keyboard_type;
            $data->keyboard_name = $request->keyboard_name;
            $data->inline_type = $request->inline_type;
            $data->keyboard_value = $request->keyboard_value ?? '-';
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
    
    //编辑状态
    public function change_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = TelegramBotKeyboard::where('rid', $request->rid)->first();
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
