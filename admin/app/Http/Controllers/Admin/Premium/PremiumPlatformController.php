<?php

namespace App\Http\Controllers\Admin\Premium;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Premium\PremiumPlatform;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Transit\TransitWallet;
use App\Http\Services\RsaServices;

class PremiumPlatformController extends Controller
{
    public $PlatformName = ['1' => '自己搭建', '2' => 'API平台调用'];
    public $Status = ['开启','关闭'];
    
    public function index(Request $request)
    {
        $PlatformName = $this->PlatformName;
        $Status = $this->Status;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.premium.platform.index',compact("PlatformName","Status","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = PremiumPlatform::from('t_premium_platform as a')
                 ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                 ->where(function($query) use ($request){
                if ($request->platform_hash != '') {
                    $query->where('a.platform_hash', 'like' ,"%" . $request->platform_hash ."%");
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $PlatformName = $this->PlatformName;
        $rsa_services = new RsaServices();
        
        $data = $data->map(function($query) use ($rsa_services,$PlatformName){
            $platform_cookie = $rsa_services->privateDecrypt($query->platform_cookie);        //解密
            $query->platform_cookie = mb_substr($platform_cookie, 0,4).'****'.mb_substr($platform_cookie, -4,4);
            $platform_phrase = $rsa_services->privateDecrypt($query->platform_phrase);        //解密
            $query->platform_phrase = mb_substr($platform_phrase, 0,4).'****'.mb_substr($platform_phrase, -4,4);
            $query->platform_name_val = $PlatformName[$query->platform_name];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = PremiumPlatform::where('bot_rid', $request->bot_rid)->where('platform_hash', $request->platform_hash)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人对应会员平台已存在');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '不能和能量钱包地址一致');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $transitdata = TransitWallet::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($transitdata)){
            return $this->responseData(400, '不能和闪兑钱包地址一致');
        }
        
        $rsa_services = new RsaServices();
        $platform_cookie = $rsa_services->publicEncrypt($request->platform_cookie);
        
        $res = PremiumPlatform::create([
            'bot_rid' => $request->bot_rid,
            'platform_name' => $request->platform_name,
            'tg_admin_uid' => $request->tg_admin_uid ?? '',
            'platform_hash' => $request->platform_hash,
            'platform_cookie' => $platform_cookie ?? '',
            'tg_notice_obj_receive' => $request->tg_notice_obj_receive ?? '',
            'tg_notice_obj_send' => $request->tg_notice_obj_send ?? '',
            'receive_wallet' => $request->receive_wallet ?? '',
            'get_tx_time' => $request->get_tx_time ?? null,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = PremiumPlatform::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = PremiumPlatform::where('bot_rid', $request->bot_rid)->where('platform_name', $request->platform_name)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '机器人对应会员平台已存在');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '不能和能量钱包地址一致');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $transitdata = TransitWallet::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($transitdata)){
            return $this->responseData(400, '不能和闪兑钱包地址一致');
        }
        
        DB::beginTransaction();
        try {
            $data = PremiumPlatform::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->platform_name = $request->platform_name;
            $data->tg_admin_uid = $request->tg_admin_uid ?? '';
            $data->platform_hash = $request->platform_hash;
            $data->tg_notice_obj_receive = $request->tg_notice_obj_receive ?? '';
            $data->tg_notice_obj_send = $request->tg_notice_obj_send ?? '';
            $data->receive_wallet = $request->receive_wallet ?? '';
            $data->get_tx_time = $request->get_tx_time ?? null;
            $data->comments = $request->comments ?? '';
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑apikey
    public function updateapikey(Request $request)
    {
        $rsa_services = new RsaServices();
        $platform_cookie = $rsa_services->publicEncrypt($request->platform_cookie);
            
        DB::beginTransaction();
        try {
            $data = PremiumPlatform::where('rid', $request->rid)->first();
            $data->platform_cookie = $platform_cookie;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑助记词
    public function updatephrase(Request $request)
    {
        $rsa_services = new RsaServices();
        $platform_phrase = $rsa_services->publicEncrypt($request->platform_phrase);
        $model = PremiumPlatform::where('rid', $request->rid)->thirdData($request->platform_phrase);
            
        DB::beginTransaction();
        try {
            $data = PremiumPlatform::where('rid', $request->rid)->first();
            $data->platform_phrase = $platform_phrase;
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
            $data = PremiumPlatform::where('rid', $request->rid)->first();
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
