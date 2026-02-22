<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformPackage;
use App\Models\Telegram\TelegramBot;
use App\Http\Controllers\Admin\Setting\ConfigController;

class EnergyPlatformPackageController extends Controller
{
    public $Status = ['开启','关闭'];
    public $EnergyDay = ['0' => '1小时','1' => '1天','3' => '3天'];
    public $PackageType = ['1' => '能量'];
    
    public function index(Request $request)
    {
        $Status = $this->Status;
        $EnergyDay = $this->EnergyDay;
        $PackageType = $this->PackageType;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.package.index',compact("botData","Status","EnergyDay","PackageType"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyPlatformPackage::from('t_energy_platform_package as a')
                ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->package_name != '') {
                    $query->where('a.package_name', 'like' ,"%" . $request->package_name ."%");
                }
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $EnergyDay = $this->EnergyDay;
        $PackageType = $this->PackageType;
        
        $data = $data->map(function($query) use ($EnergyDay,$PackageType){
            $query->energy_day_val = $EnergyDay[$query->energy_day];
            $query->package_type_val = $PackageType[$query->package_type];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        if(empty($request->trx_price) || $request->trx_price <= 0){
            return $this->responseData(400, 'trx售价不能小于0');
        }
        
        if(empty($request->energy_amount) || $request->energy_amount < 65000){
            return $this->responseData(400, '能量数量不能低于65000');
        }
        
        if(empty($request->package_name)){
            return $this->responseData(400, '套餐名称不能为空');
        }
        
        $res = EnergyPlatformPackage::create([
            'bot_rid' => $request->bot_rid,
            'package_type' => $request->package_type,
            'package_name' => $request->package_name,
            'energy_amount' => $request->energy_amount,
            'energy_day' => $request->energy_day,
            'trx_price' => $request->trx_price ?? 0.1,
            'agent_trx_price' => $request->agent_trx_price ?? 0,
            'seq_sn' => $request->seq_sn ?? 0,
            'callback_data' => 'energy_'.md5(nowDate()),
            'show_notes' => $request->show_notes ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyPlatformPackage::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }
    
    //批量删除
    public function batchdelete(Request $request)
    {
        $res = EnergyPlatformPackage::where('bot_rid', $request->bot_rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request, ConfigController $upload)
    {
        if(empty($request->trx_price) || $request->trx_price <= 0){
            return $this->responseData(400, 'trx售价不能小于0');
        }
        
        if(empty($request->energy_amount) || $request->energy_amount < 65000){
            return $this->responseData(400, '能量数量不能低于65000');
        }
        
        if(empty($request->package_name)){
            return $this->responseData(400, '套餐名称不能为空');
        }
        
        $packageData = EnergyPlatformPackage::where('rid', $request->rid)->first();
        if(empty($packageData)){
            return $this->responseData(400, '数据不存在');
        }
        
        if(!empty($request->file('thumb'))){
            $filedata = $upload->uploadfile($request->file('thumb'), 'news');
            $fileurl = $filedata['data']['url'];
        }else{
            $fileurl = $packageData->reply_photo;
        }
        
        DB::beginTransaction();  
        try {
            
            $packageData->bot_rid = $request->bot_rid;
            $packageData->package_type = $request->package_type;
            $packageData->package_name = $request->package_name;
            $packageData->energy_amount = $request->energy_amount;
            $packageData->energy_day = $request->energy_day;
            $packageData->trx_price = $request->trx_price ?? 0.1;
            $packageData->agent_trx_price = $request->agent_trx_price ?? 0;
            $packageData->seq_sn = $request->seq_sn ?? 0;
            $packageData->show_notes = $request->show_notes ?? '';
            $packageData->package_pic = $fileurl;
            $packageData->update_time = nowDate();
            $packageData->save();
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
            $data = EnergyPlatformPackage::where('rid', $request->rid)->first();
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
        $Status = $this->Status;
        $EnergyDay = $this->EnergyDay;
        $PackageType = $this->PackageType;
        
        $data = EnergyPlatformPackage::from('t_energy_platform_package as a')
                 ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                 ->where('a.rid',$request->rid)
                 ->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')
                 ->first();
            
        return view('admin.energy.package.edit',compact("Status","EnergyDay","PackageType","data"));
        
    }
    
    //复制
    public function copyPaste(Request $request)
    {   
        if(empty($request->paste_bot_rid) || empty($request->copy_bot_rid)){
            return $this->responseData(400, '覆盖和来源机器人必填');
        }
        
        if($request->paste_bot_rid == $request->copy_bot_rid){
            return $this->responseData(400, '覆盖和来源机器人不能一致');
        }
        
        $data = EnergyPlatformPackage::where('bot_rid', $request->copy_bot_rid)->get();
        
        if($data->count() == 0){
            return $this->responseData(400, '来源机器人无数据可复制');
        }
        
        DB::beginTransaction();
        try {
            EnergyPlatformPackage::where('bot_rid', $request->paste_bot_rid)->delete();
            
            EnergyPlatformPackage::insertUsing([
                'bot_rid', 'package_type', 'package_name','energy_amount','energy_day','trx_price','agent_trx_price','status','seq_sn','create_time','callback_data','show_notes','package_pic'
            ], EnergyPlatformPackage::selectRaw(
                "$request->paste_bot_rid, package_type, package_name, energy_amount, energy_day, trx_price, agent_trx_price, status, seq_sn, sysdate(),concat('energy_',md5(rand())), show_notes, package_pic"
            )->where('bot_rid', $request->copy_bot_rid));
            
            DB::commit();
            return $this->responseData(200, '复制成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败'.$e->getMessage());
        }
        
    }
}
