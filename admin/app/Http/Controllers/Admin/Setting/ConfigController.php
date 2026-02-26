<?php

namespace App\Http\Controllers\Admin\Setting;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use App\Models\System\SysConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    public function index()
    {
        $data = SysConfig::get();
        return view('admin.setting.config.index', compact('data'));
    }

    public function update(Request $request)
    {
        $data = $request->all(); 
        foreach ($data as $k => $v) {
            if (in_array($k, ['_token', 'config_type'])) {
                continue;
            }
            if($v == ''){
                return $this->responseData(400, '请把内容全部填写');
            }
        }

        DB::beginTransaction();
        try{
            $admin_id = Auth::guard('admin')->id();
            $time = nowDate();
            foreach ($data as $k => $v) {
                if (in_array($k, ['_token', 'config_type'])) {
                    continue;
                }
                if(is_array($v)){
                    $v = json_encode($v);
                }

                // 新增：支持新增配置项（如 TRON API key），如果不存在则创建
                $config = SysConfig::where('config_key', $k)->first();
                if ($config) {
                    $config->config_val = $v;
                    $config->update_by = (string)$admin_id;
                    $config->update_time = $time;
                    $config->save();
                } else {
                    // 仅为新 key 设置一个基础备注，避免 NOT NULL 约束报错
                    $comments = '系统配置项 ' . $k;
                    if ($k === 'tronscan_api_keys') {
                        $comments = 'TRONSCAN API Keys，逗号分隔';
                    } elseif ($k === 'trongrid_api_keys') {
                        $comments = 'TRONGRID API Keys，逗号分隔';
                    }

                    SysConfig::create([
                        'config_key'   => $k,
                        'config_val'   => $v,
                        'comments'     => $comments,
                        'create_by'    => (string)$admin_id,
                        'create_time'  => $time,
                        'update_by'    => (string)$admin_id,
                        'update_time'  => $time,
                    ]);
                }
            }

            DB::commit();
            return $this->responseData(200, 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, $e->getMessage());
        }
    }
    
    public function uploadfile($file, $disk)
    {
        if (!$file->isValid()) {
            return $this->responseData(400, '文件不合法');
        }
        //原文件名
        $originalName = $file->getClientOriginalName();
        //扩展名
        $ext = $file->getClientOriginalExtension();
        //MimeType
        $type = $file->getClientMimeType();
        //临时绝对路径
        $realPath = $file->getRealPath();
        $filename = date('Ymd') . '/' . date('YmdHis') . rand(100, 1000) . '.' . $ext;
        $res = Storage::disk($disk)->put($filename, file_get_contents($realPath));
        
        $url  = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $url .= "://" . $_SERVER['HTTP_HOST'];
        return $res ? $this->responseData(200, 'success', ['url' => $url . '/uploads/' . $disk . '/' . $filename]) : $this->responseData(400, 'error');
    }
    
    public function clearjobcache(Request $request)
    {
        $data = SysConfig::where('config_key','job_url')->first();

        if(empty($data)){
            return $this->responseData(400, '请先配置任务域名url.');
        }
        if(empty($data->config_val->url) || $data->config_val->url == '' || $data->config_val->url == null){
            return $this->responseData(400, '请先配置任务域名url');
        }
        
        $url = $data->config_val->url.'/api/config/clear_timing';

        $AipHttpClient = new AipHttpClient();
        
        $res = $AipHttpClient->postnew($url);
        
        if(empty($res)){
            return $this->responseData(400, '检查任务是否已启动1');
        }else{
            $res = json_decode($res,true);
            if(empty($res['code'])){
                return $this->responseData(400, '检查任务是否已启动2');
            }else{
                if($res['code'] == 200){
                    return $this->responseData(200, 'success');
                }else{
                    return $this->responseData(400, '检查任务是否已启动3');
                }
            }
        }
    }
    
    public function checkjob(Request $request)
    {
        $data = SysConfig::where('config_key','job_url')->first();

        if(empty($data)){
            return $this->responseData(400, '请先配置任务域名url.');
        }
        if(empty($data->config_val->url) || $data->config_val->url == '' || $data->config_val->url == null){
            return $this->responseData(400, '请先配置任务域名url');
        }
        
        $url = $data->config_val->url.'/api/config/check_status';

        $AipHttpClient = new AipHttpClient();
        
        $res = $AipHttpClient->postnew($url);
        
        if(empty($res)){
            return $this->responseData(400, '已停止1');
        }else{
            $res = json_decode($res,true);
            if(empty($res['code'])){
                return $this->responseData(400, '已停止2');
            }else{
                if($res['code'] == 200){
                    return $this->responseData(200, '运行正常');
                }else{
                    return $this->responseData(400, '已停止3');
                }
            }
        }
    }
}
