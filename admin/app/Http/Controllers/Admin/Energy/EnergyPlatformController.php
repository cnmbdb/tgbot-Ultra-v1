<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyPlatform;
use App\Models\Telegram\TelegramBot;
use App\Http\Services\RsaServices;

class EnergyPlatformController extends Controller
{
    public $PlatformName = ['1' => 'Neee.cc','2' => 'RentEnergysBot','3' => '自己质押代理','4' => 'trongas.io(平台已关闭)','5' => '机器人开发代理(平台已关闭)','6' => 'Sohu搜狐'];
    public $Status = ['开启','关闭'];
    public $PollGroup = ['A' => 'A组','B' => 'B组','C' => 'C组','D' => 'D组','E' => 'E组','F' => 'F组','G' => 'G组'];
    
    public function index(Request $request)
    {
        $PlatformName = $this->PlatformName;
        $Status = $this->Status;
        $PollGroup = $this->PollGroup;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.platform.index',compact("PlatformName","Status","PollGroup","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyPlatform::from('t_energy_platform as a')
                ->join('t_telegram_bot as b','a.tg_notice_bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->platform_uid != '') {
                    $query->where('a.platform_uid', 'like' ,"%" . $request->platform_uid ."%");
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $PlatformName = $this->PlatformName;
        $PollGroup = $this->PollGroup;
        $rsa_services = new RsaServices();
        
        $data = $data->map(function($query) use ($rsa_services,$PlatformName,$PollGroup){
            $platform_apikey = $rsa_services->privateDecrypt($query->platform_apikey);        //解密
            $query->platform_apikey = mb_substr($platform_apikey, 0,4).'****'.mb_substr($platform_apikey, -4,4);
            $query->platform_name_val = $PlatformName[$query->platform_name];
            $query->poll_group_val = $PollGroup[$query->poll_group];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        $res = EnergyPlatform::create([
            'poll_group' => $request->poll_group,
            'platform_name' => $request->platform_name,
            'platform_uid' => $request->platform_uid,
            'alert_platform_balance' => $request->alert_platform_balance ?? 0,
            'tg_notice_obj' => $request->tg_notice_obj ?? '',
            'tg_notice_bot_rid' => $request->tg_notice_bot_rid ?? '',
            'seq_sn' => $request->seq_sn ?? 0,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyPlatform::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = EnergyPlatform::where('rid', $request->rid)->first();
            $data->poll_group = $request->poll_group;
            $data->platform_name = $request->platform_name;
            $data->platform_uid = $request->platform_uid;
            $data->alert_platform_balance = $request->alert_platform_balance ?? 0;
            $data->tg_notice_obj = $request->tg_notice_obj ?? '';
            $data->tg_notice_bot_rid = $request->tg_notice_bot_rid ?? '';
            $data->seq_sn = $request->seq_sn ?? 0;
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
        $platform_apikey = $rsa_services->publicEncrypt($request->platform_apikey);
            
        DB::beginTransaction();
        try {
            $data = EnergyPlatform::where('rid', $request->rid)->first();
            $model = EnergyPlatform::where('rid', $request->rid)
                    ->secondData($request->platform_apikey,$data->platform_uid);
            $data->platform_apikey = $platform_apikey;
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
            $data = EnergyPlatform::where('rid', $request->rid)->first();
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
