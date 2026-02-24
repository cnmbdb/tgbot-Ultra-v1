<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformOrder;
use App\Http\Services\RsaServices;

class EnergyPlatformOrderController extends Controller
{
    public $EnergyDay = ['0' => '1小时','1' => '1天','3' => '3天','30' => '30天'];
    public $PlatformName = ['1' => 'Neee.cc','2' => 'RentEnergysBot','3' => '自己质押代理','4' => 'trongas.io','5' => '机器人开发代理','7' => 'NL-API'];
    public $SourceType = ['1' => '人工下单','2' => '自动下单','3' => '智能托管', '4' => '笔数套餐'];
    public $RecoveryStatus = ['1' => '不用回收','2' => '待回收','3' => '已回收'];
    
    public function index(Request $request)
    {
        $PlatformName = EnergyPlatform::pluck('rid','rid'); 
        $SourceType = $this->SourceType;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.order.index',compact("PlatformName","SourceType","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyPlatformOrder::from('t_energy_platform_order as a')
                ->leftjoin('t_energy_platform_bot as b','a.energy_platform_bot_rid','b.rid')
                ->leftjoin('t_telegram_bot as c','b.bot_rid','c.rid')
                ->where(function($query) use ($request){
                if ($request->platform_uid != '') {
                    $query->where('a.platform_uid', 'like' ,"%" . $request->platform_uid ."%");
                }
                if ($request->platform_order_id != '') {
                    $query->where('a.platform_order_id', 'like' ,"%" . $request->platform_order_id ."%");
                }
                if ($request->bot_rid != '') {
                    $query->where('b.bot_rid', $request->bot_rid);
                }
                if ($request->receive_address != '') {
                    $query->where('a.receive_address', $request->receive_address);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','c.bot_username')->orderBy('a.rid','desc')->get();
        
        $PlatformName = $this->PlatformName;
        $EnergyDay = $this->EnergyDay;
        $SourceType = $this->SourceType;
        $RecoveryStatus = $this->RecoveryStatus;
        
        $data = $data->map(function($query) use ($PlatformName,$EnergyDay,$SourceType,$RecoveryStatus){
            $query->platform_name_val = $PlatformName[$query->platform_name];
            $query->energy_day_val = $EnergyDay[$query->energy_day];
            $query->source_type_val = $SourceType[$query->source_type];
            $query->recovery_status_val = $RecoveryStatus[$query->recovery_status];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //批量回收地址能量
    public function batchRecoveryEnergy(Request $request)
    {
        if(empty($request->daili_address) || empty($request->recovery_address)){
            return $this->responseData(400, '代理地址或接收地址错误');
        }
        
        $dailiData = EnergyPlatform::where('platform_uid', $request->daili_address)->first();
        if(empty($dailiData)){
            return $this->responseData(400, '代理地址不存在');
        }
        
        if(empty($dailiData)){
            return $this->responseData(400, '代理地址未配置私钥');
        }
        
        $rsa_services = new RsaServices();
        $platform_apikey = $rsa_services->privateDecrypt($dailiData->platform_apikey);        //解密
        if(empty($platform_apikey)){
            return $this->responseData(400, '代理地址未配置私钥');
        }
        
        $dailiOrder = EnergyPlatformOrder::where('platform_uid', $request->daili_address)->where('receive_address' ,$request->recovery_address)->where('recovery_status', 2)->get();
        
        if($dailiOrder->count() == 0){
            return $this->responseData(400, '地址无代理能量,无需回收');
        }
        
        $total_recovery = $dailiOrder->sum('use_trx');
        
        DB::beginTransaction();  
        try {
            // 调用接口回收
            $params = [
                'pri' => $platform_apikey,
                'fromaddress' => $request->daili_address,
                'receiveaddress' => $request->recovery_address,
                'resourcename' => 'ENERGY',
                'resourceamount' => $total_recovery,
                'resourcetype' => 3, //资源方式：1代理资源,2回收资源(按能量),3回收资源(按TRX)
                'permissionid' => $dailiData->permission_id
            ];
            
            $apiWebUrl = config('services.api_web.url');
            $res = Get_Curl($apiWebUrl . '/api/tron/delegaandundelete',$params);
            
            if(empty($res)){
                DB::rollBack();
                return $this->responseData(400, '回收失败,接口返回空');
            }else{
                $res = json_decode($res,true);
                
                if($res['code'] && $res['code'] != 200){
                    DB::rollBack();
                    return $this->responseData(400, '回收失败,接口返回'.$res['msg']);
                }
            }
            
            $save_data = [];
            $save_data['recovery_status'] = 3;
            $save_data['recovery_time'] = nowDate();
            EnergyPlatformOrder::whereIn('rid',array_column(json_decode($dailiOrder,true), 'rid'))->update($save_data);
            
            DB::commit();
            return $this->responseData(200, '回收成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '回收失败'.$e->getMessage());
        }
    }
    
    //修改该笔为已回收
    public function alreadyrecover(Request $request)
    {
        $dailiOrder = EnergyPlatformOrder::where('rid', $request->rid)->where('recovery_status', 2)->first();
        
        if(empty($dailiOrder)){
            return $this->responseData(400, '仅待回收可更改为已回收');
        }
        
        DB::beginTransaction();  
        try {
            $save_data = [];
            $save_data['recovery_status'] = 3;
            $save_data['recovery_time'] = nowDate();
            EnergyPlatformOrder::where('rid',$request->rid)->update($save_data);
            
            DB::commit();
            return $this->responseData(200, '更改回收成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更改回收失败'.$e->getMessage());
        }
    }
}
