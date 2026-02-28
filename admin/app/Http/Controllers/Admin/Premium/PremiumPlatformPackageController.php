<?php

namespace App\Http\Controllers\Admin\Premium;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Premium\PremiumPlatform;
use App\Models\Telegram\TelegramBot;
use App\Models\Premium\PremiumPlatformPackage;
use App\Http\Controllers\Admin\Setting\ConfigController;

class PremiumPlatformPackageController extends Controller
{
    public $PlatformName = ['1' => '自己搭建'];
    public $Status = ['开启','关闭'];
    public $PackageMonth = ['3' => '3个月','6' => '6个月','12' => '12个月'];
    
    public function index(Request $request)
    {
        $PlatformName = PremiumPlatform::pluck('rid','rid'); 
        $Status = $this->Status;
        $PackageMonth = $this->PackageMonth;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.premium.package.index',compact("PlatformName","Status","PackageMonth","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = PremiumPlatformPackage::from('t_premium_platform_package as a')
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
        
        $PackageMonth = $this->PackageMonth;
        
        $data = $data->map(function($query) use ($PackageMonth){
            $query->package_month_val = $PackageMonth[$query->package_month];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        if(empty($request->usdt_price) || $request->usdt_price <= 0){
            return $this->responseData(400, 'usdt售价不能小于0');
        }
        
        if(empty($request->package_name)){
            return $this->responseData(400, '套餐名称不能为空');
        }
        
        $data = PremiumPlatform::where('rid',$request->premium_platform_rid)->first();
        if(empty($data)){
            return $this->responseData(400, '会员平台不存在');
        }
        
        $res = PremiumPlatformPackage::create([
            'bot_rid' => $data->bot_rid,
            'premium_platform_rid' => $request->premium_platform_rid,
            'package_name' => $request->package_name,
            'package_month' => $request->package_month,
            'usdt_price' => $request->usdt_price ?? 0.1,
            'seq_sn' => $request->seq_sn ?? 0,
            'callback_data' => 'premium_'.md5(nowDate()),
            'show_notes' => $request->show_notes ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = PremiumPlatformPackage::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request, ConfigController $upload)
    {
        if(empty($request->usdt_price) || $request->usdt_price <= 0){
            return $this->responseData(400, 'usdt售价不能小于0');
        }

        if(empty($request->package_name)){
            return $this->responseData(400, '套餐名称不能为空');
        }
        
        $data = PremiumPlatform::where('rid',$request->premium_platform_rid)->first();
        if(empty($data)){
            return $this->responseData(400, '会员平台不存在');
        }
        
        $packageData = PremiumPlatformPackage::where('rid', $request->rid)->first();
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
            
            $packageData->bot_rid = $data->bot_rid;
            $packageData->premium_platform_rid = $request->premium_platform_rid;
            $packageData->package_name = $request->package_name;
            $packageData->package_month = $request->package_month;
            $packageData->usdt_price = $request->usdt_price ?? 0.1;
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
            $data = PremiumPlatformPackage::where('rid', $request->rid)->first();
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
        $PlatformName = PremiumPlatform::pluck('rid','rid'); 
        $Status = $this->Status;
        $PackageMonth = $this->PackageMonth;
        
        $data = PremiumPlatformPackage::from('t_premium_platform_package as a')
                 ->leftJoin('t_telegram_bot as b','a.bot_rid','b.rid')
                 ->where('a.rid',$request->rid)
                 ->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')
                 ->first();
            
        return view('admin.premium.package.edit',compact("PlatformName","Status","PackageMonth","data"));
        
    }
    
    //复制
    public function copyPaste(Request $request)
    {   
        if(empty($request->copy_premium_platform_rid) || empty($request->paste_premium_platform_rid)){
            return $this->responseData(400, '覆盖和来源会员平台必填');
        }
        
        if($request->copy_premium_platform_rid == $request->paste_premium_platform_rid){
            return $this->responseData(400, '覆盖和来源会员平台不能一致');
        }
        
        $copyData = PremiumPlatform::where('rid', $request->copy_premium_platform_rid)->first();
        
        if(empty($copyData)){
            return $this->responseData(400, '来源会员平台不存在');
        }
        
        $pasteData = PremiumPlatform::where('rid', $request->paste_premium_platform_rid)->first();
        
        if(empty($pasteData)){
            return $this->responseData(400, '覆盖会员平台不存在');
        }
        
        $data = PremiumPlatformPackage::where('premium_platform_rid', $request->copy_premium_platform_rid)->get();
        
        if($data->count() == 0){
            return $this->responseData(400, '来源会员平台无数据可复制');
        }
        
        DB::beginTransaction();
        try {
            PremiumPlatformPackage::where('premium_platform_rid', $request->paste_premium_platform_rid)->delete();
            
            PremiumPlatformPackage::insertUsing([
                'bot_rid', 'premium_platform_rid', 'package_name', 'package_month','usdt_price','callback_data','seq_sn','status','show_notes','package_pic','comments','create_time'
            ], PremiumPlatformPackage::selectRaw(
                "$pasteData->bot_rid, $request->paste_premium_platform_rid, package_name, package_month, usdt_price, concat('premium_',md5(rand())), seq_sn, status, show_notes, package_pic, comments, now()"
            )->where('premium_platform_rid', $request->copy_premium_platform_rid));
            
            DB::commit();
            return $this->responseData(200, '复制成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败'.$e->getMessage());
        }
        
    }
}
