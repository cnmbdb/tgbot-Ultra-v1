<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotKeyreply;
use App\Models\Premium\PremiumPlatform;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Transit\TransitWallet;
use Telegram\Bot\Api;

class TelegrambotController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.telegram.telegrambot.index');
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TelegramBot::where(function($query) use ($request){
            if ($request->bot_username != '') {
                $query->where('bot_username', 'like' ,"%" . $request->bot_username ."%");
            }      
        });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->orderBy('rid','desc')->get();

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TelegramBot::where('bot_token', $request->bot_token)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人已存在');
        }
        
        $res = TelegramBot::create([
            'bot_token' => $request->bot_token,
            'bot_admin_username' => $request->bot_admin_username,
            'comments' => $request->comments,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $data = TelegramBotKeyreply::where('bot_rid', $request->rid);
        if($data->count() > 0){
            return $this->responseData(400, '请先删除关键字');
        }
        
        
        $res = TelegramBot::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {   
        $data = TelegramBot::where('bot_token', $request->bot_token)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = TelegramBot::where('rid', $request->rid)->first();
            $data->bot_token = $request->bot_token;
            $data->bot_admin_username = $request->bot_admin_username;
            $data->comments = $request->comments;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }
    
    //注册webhook
    public function regwebhook(Request $request)
    {
        $data = TelegramBot::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        $domain = 'https://'. $_SERVER['HTTP_HOST'] .'/api/telegram/getdata?rid='. $request->rid;
        $url = 'https://api.telegram.org/bot'. $data->bot_token .'/setWebhook?url='. $domain;
        
        $http = new AipHttpClient();
        $result = $http->get($url);
        // llog($result,'single');
    
        if($result['code'] != 200){
            return $this->responseData(400, '请求失败,需要外网权限');
        }
        
        $res = json_decode($result['content'],true);

        if($res['ok'] && $res['result']){
            $desc = $res['description'];
            return $this->responseData(200, $desc);
        }
        
        return $this->responseData(400, 'webhook设置失败');
    }
    
    //更新机器人信息
    public function gengxin(Request $request)
    {
        $data = TelegramBot::where('rid', $request->rid)->first();
        if(empty($data)){
            return $this->responseData(400, '数据不存在');
        }
        
        $telegram = new Api($data->bot_token);
        $response = $telegram->getMe();

        // $botId = $response->getId();
        $firstName = $response->getFirstName();
        $username = $response->getUsername();
        
        $res = TelegramBot::where('rid', $request->rid)->update([
            'bot_firstname' => $firstName,
            'bot_username' => $username,
            'update_time' => nowDate()
        ]);
        
        return $res ? $this->responseData(200, '更新成功') : $this->responseData(400, '更新失败');
    }
    
    //编辑充值
    public function recharge(Request $request)
    {   
        $data = TelegramBot::where('recharge_wallet_addr', $request->recharge_wallet_addr)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '充值钱包地址已存在');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->recharge_wallet_addr)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '不能和能量钱包地址一致');
        }
        
        $premiumdata = PremiumPlatform::where('receive_wallet', $request->recharge_wallet_addr)->first();
        if(!empty($premiumdata)){
            return $this->responseData(400, '不能和会员钱包地址一致');
        }
        
        $transitdata = TransitWallet::where('receive_wallet', $request->recharge_wallet_addr)->first();
        if(!empty($transitdata)){
            return $this->responseData(400, '不能和闪兑钱包地址一致');
        }
        
        DB::beginTransaction();
        try {
            $data = TelegramBot::where('rid', $request->rid)->first();
            $data->recharge_wallet_addr = $request->recharge_wallet_addr;
            $data->get_tx_time = $request->get_tx_time;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }
}
