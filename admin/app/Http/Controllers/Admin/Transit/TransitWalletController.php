<?php

namespace App\Http\Controllers\Admin\Transit;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Transit\TransitWallet;
use App\Models\Transit\TransitWalletCoin;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Premium\PremiumPlatform;
use App\Http\Services\RsaServices;

class TransitWalletController extends Controller
{
    public $ChainType = ['trc' => 'trc'];
    public $TransWalletStatus = ['开启','关闭'];
    
    public function index(Request $request)
    {
        $ChainType = $this->ChainType;
        $TransWalletStatus = $this->TransWalletStatus;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.transit.wallet.index',compact("ChainType","TransWalletStatus","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = TransitWallet::from('transit_wallet as a')
                 ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                 ->where(function($query) use ($request){
                if ($request->receive_wallet != '') {
                    $query->where('a.receive_wallet', 'like' ,"%" . $request->receive_wallet ."%");
                }   
                if ($request->send_wallet != '') {
                    $query->where('a.send_wallet', 'like' ,"%" . $request->send_wallet ."%");
                }  
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $rsa_services = new RsaServices();
        $data = $data->map(function($query) use ($rsa_services){
            $send_wallet_privatekey = $rsa_services->privateDecrypt($query->send_wallet_privatekey);        //解密
            $query->send_wallet_privatekey = mb_substr($send_wallet_privatekey, 0,4).'****'.mb_substr($send_wallet_privatekey, -4,4);
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $data = TransitWallet::where('receive_wallet', $request->receive_wallet)->where('chain_type', $request->chain_type)->first();
        if(!empty($data)){
            return $this->responseData(400, '收款钱包已存在');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '不能和能量钱包地址一致');
        }
        
        $premiumdata = PremiumPlatform::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($premiumdata)){
            return $this->responseData(400, '不能和会员钱包地址一致');
        }
        
        $res = TransitWallet::create([
            'bot_rid' => $request->bot_rid,
            'chain_type' => $request->chain_type,
            'receive_wallet' => $request->receive_wallet,
            'send_wallet' => $request->send_wallet,
            'show_notes' => $request->show_notes,
            'auto_stock_min_trx' => $request->auto_stock_min_trx,
            'auto_stock_per_usdt' => $request->auto_stock_per_usdt,
            'tg_notice_obj_receive' => $request->tg_notice_obj_receive,
            'tg_notice_obj_send' => $request->tg_notice_obj_send,
            'get_tx_time' => $request->get_tx_time,
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $data = TransitWalletCoin::where('transit_wallet_id', $request->rid);
        if($data->count() > 0){
            return $this->responseData(400, '请先删除钱包闪兑币种');
        }
        
        $res = TransitWallet::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        $data = TransitWallet::where('receive_wallet', $request->receive_wallet)->where('chain_type', $request->chain_type)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '收款钱包已存在');
        }
        
        $botdata = TelegramBot::where('recharge_wallet_addr', $request->receive_wallet)->first();
        if(!empty($botdata)){
            return $this->responseData(400, '收款钱包不能和机器人充值地址一致');
        }
        
        $energydata = EnergyPlatformBot::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($energydata)){
            return $this->responseData(400, '不能和能量钱包地址一致');
        }
        
        $premiumdata = PremiumPlatform::where('receive_wallet', $request->receive_wallet)->first();
        if(!empty($premiumdata)){
            return $this->responseData(400, '不能和会员钱包地址一致');
        }
        
        DB::beginTransaction();
        try {
            $data = TransitWallet::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->chain_type = $request->chain_type;
            $data->receive_wallet = $request->receive_wallet;
            $data->send_wallet = $request->send_wallet;
            $data->show_notes = $request->show_notes;
            $data->auto_stock_min_trx = $request->auto_stock_min_trx;
            $data->auto_stock_per_usdt = $request->auto_stock_per_usdt;
            $data->tg_notice_obj_receive = $request->tg_notice_obj_receive;
            $data->tg_notice_obj_send = $request->tg_notice_obj_send;
            $data->get_tx_time = $request->get_tx_time;
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
            $data = TransitWallet::where('rid', $request->rid)->first();
            $data->status = $request->status == 1 ? 0 : 1;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑私钥
    public function updateprikey(Request $request)
    {
        $rsa_services = new RsaServices();
        $model = TransitWallet::where('rid', $request->rid)->firstData($request->send_wallet_privatekey);
        $PRIVATE_KEY = $rsa_services->publicEncrypt($request->send_wallet_privatekey);
            
        DB::beginTransaction();
        try {
            $data = TransitWallet::where('rid', $request->rid)->first();
            $data->send_wallet_privatekey = $PRIVATE_KEY;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //授权
    public function approve(Request $request)
    {
        $data = TransitWallet::where('rid', $request->rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '收款钱包不存在');
        }
        
        $rsa_services = new RsaServices();
        $PRIVATE_KEY = $rsa_services->privateDecrypt($data->send_wallet_privatekey);
        
        $para = [
            'fromaddress' => $data->send_wallet,
            'pri' => $PRIVATE_KEY,
            'approveddress' => 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE', //sunswap合约地址
            'trc20ContractAddress' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', //usdt合约地址
            'approvetype' => 1, //1表示授权
        ];
        
        $apiWebUrl = config('services.api_web.url');
        $res = Get_Curl($apiWebUrl . '/api/tron/approve', $para);
        
        if(empty($res)){
            return $this->responseData(400, '授权失败1');
        }else{
            $res = json_decode($res,true);
            if(empty($res['code'])){
                return $this->responseData(400, '授权失败2');
            }else{
                if($res['code'] == 200){
                    return $this->responseData(200, '授权成功,查看链上是否授权成功');
                }else{
                    return $this->responseData(400, '授权失败3,检查私钥或者trx是否足够');
                }
            }
        }
    }
    
    //手工进货
    public function manualtrx(Request $request)
    {
        if($request->swapamount <= 0 || !is_numeric($request->swapamount)){
            return $this->responseData(400, '请输入正确的usdt数量');
        }
        
        $data = TransitWallet::where('rid', $request->rid)->first();
        
        if(empty($data)){
            return $this->responseData(400, '收款钱包不存在');
        }
        
        $rsa_services = new RsaServices();
        $PRIVATE_KEY = $rsa_services->privateDecrypt($data->send_wallet_privatekey);
        
        $para = [
            'address' => $data->send_wallet,
            'pri1' => $PRIVATE_KEY,
            'swapamount' => $request->swapamount, //进货usdt金额
            'swapplatform' => 'sunswap', //闪兑平台为:sunswap或者bkswap，推荐用sunswap,矿工费低一些
            'pri2' => '',
            'pri3' => '',
            'pri4' => '',
            'pri5' => ''
        ];
        
        $apiWebUrl = config('services.api_web.url');
        $res = Get_Curl($apiWebUrl . '/api/tron/swap', $para);
        
        if(empty($res)){
            return $this->responseData(400, '进货失败1');
        }else{
            $res = json_decode($res,true);
            if(empty($res['code'])){
                return $this->responseData(400, '进货失败2');
            }else{
                if($res['code'] == 200){
                    return $this->responseData(200, '进货成功');
                }else{
                    return $this->responseData(400, '进货失败3：'.$res['msg']);
                }
            }
        }
    }
}
