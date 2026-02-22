<?php

namespace App\Http\Controllers\Admin\Collection;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Collection\CollectionWallet;
use App\Http\Services\RsaServices;

class CollectionWalletController extends Controller
{
    public $ChainType = ['trc' => 'trc'];
    public $CollectionWalletStatus = ['开启','关闭'];
    
    public function index(Request $request)
    {
        $ChainType = $this->ChainType;
        $CollectionWalletStatus = $this->CollectionWalletStatus;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.collection.wallet.index',compact("ChainType","CollectionWalletStatus","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = CollectionWallet::from('collection_wallet as a')
                ->leftJoin('telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->wallet_addr != '') {
                    $query->where('a.wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
                }   
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $rsa_services = new RsaServices();
        $data = $data->map(function($query) use ($rsa_services){
            $wallet_addr_privatekey = $rsa_services->privateDecrypt($query->wallet_addr_privatekey);        //解密
            $query->wallet_addr_privatekey = mb_substr($wallet_addr_privatekey, 0,4).'****'.mb_substr($wallet_addr_privatekey, -4,4);
            return $query;
        });
        
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        if($request->usdt_reserve_amount != 0 && $request->usdt_collection_amount != 0 && $request->usdt_reserve_amount >= $request->usdt_collection_amount){
            return $this->responseData(400, 'USDT预留金额不能大于归集金额');
        }
        
        if($request->trx_reserve_amount != 0 && $request->trx_collection_amount != 0 && $request->trx_reserve_amount >= $request->trx_collection_amount){
            return $this->responseData(400, 'TRX预留金额不能大于归集金额');
        }

        if($request->wallet_addr == $request->collection_wallet_addr){
            return $this->responseData(400, '钱包和归集钱包不能一样');
        }

        $data = CollectionWallet::where('wallet_addr', $request->wallet_addr)->where('chain_type', $request->chain_type)->first();
        if(!empty($data)){
            return $this->responseData(400, '归集钱包已存在');
        }
        
        if(empty($request->chain_type) || empty($request->wallet_addr)){
            return $this->responseData(400, '有必填项未填写,请检查');
        }
        
        $res = CollectionWallet::create([
            'bot_rid' => $request->bot_rid,
            'chain_type' => $request->chain_type,
            'wallet_addr' => $request->wallet_addr,
            'tg_notice_obj' => $request->tg_notice_obj,
            'trx_collection_amount' => $request->trx_collection_amount ?? 0,
            'usdt_collection_amount' => $request->usdt_collection_amount ?? 0,
            'trx_reserve_amount' => $request->trx_reserve_amount ?? 0,
            'usdt_reserve_amount' => $request->usdt_reserve_amount ?? 0,
            'collection_wallet_addr' => $request->collection_wallet_addr ?? '',
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = CollectionWallet::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        if($request->usdt_reserve_amount != 0 && $request->usdt_collection_amount != 0 && $request->usdt_reserve_amount >= $request->usdt_collection_amount){
            return $this->responseData(400, 'USDT预留金额不能大于归集金额');
        }
        
        if($request->trx_reserve_amount != 0 && $request->trx_collection_amount != 0 && $request->trx_reserve_amount >= $request->trx_collection_amount){
            return $this->responseData(400, 'TRX预留金额不能大于归集金额');
        }
        
        if($request->wallet_addr == $request->collection_wallet_addr){
            return $this->responseData(400, '钱包和归集钱包不能一样');
        }
        
        $data = CollectionWallet::where('wallet_addr', $request->wallet_addr)->where('chain_type', $request->chain_type)->where('rid','<>',$request->rid)->first();
        if(!empty($data)){
            return $this->responseData(400, '归集钱包已存在');
        }
        
        DB::beginTransaction();
        try {
            $data = CollectionWallet::where('rid', $request->rid)->first();
            $data->bot_rid = $request->bot_rid;
            $data->chain_type = $request->chain_type;
            $data->wallet_addr = $request->wallet_addr;
            $data->tg_notice_obj = $request->tg_notice_obj;
            $data->trx_collection_amount = $request->trx_collection_amount ?? 0;
            $data->usdt_collection_amount = $request->usdt_collection_amount ?? 0;
            $data->trx_reserve_amount = $request->trx_reserve_amount ?? 0;
            $data->usdt_reserve_amount = $request->usdt_reserve_amount ?? 0;
            $data->collection_wallet_addr = $request->collection_wallet_addr ?? '';
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
    
    //编辑私钥
    public function updateprikey(Request $request)
    {
        $rsa_services = new RsaServices();
        $model = CollectionWallet::where('rid', $request->rid)->fourData($request->wallet_addr_privatekey);
        $PRIVATE_KEY = $rsa_services->publicEncrypt($request->wallet_addr_privatekey);
            
        DB::beginTransaction();
        try {
            $data = CollectionWallet::where('rid', $request->rid)->first();
            $data->wallet_addr_privatekey = $PRIVATE_KEY;
            $data->permission_id = $request->permission_id ?? 0;
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
            $data = CollectionWallet::where('rid', $request->rid)->first();
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
