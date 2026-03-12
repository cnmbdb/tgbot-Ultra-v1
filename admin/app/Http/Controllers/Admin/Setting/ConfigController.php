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
    public function index(Request $request)
    {
        $data = SysConfig::get();

        // 获取授权信息
        $licenseInfo = $this->getLicenseInfo($data);

        // 未授权时从菜单跳转过来，默认打开「授权激活」标签
        $showActivateTab = $request->get('activate') == '1';

        return view('admin.setting.config.index', compact('data', 'licenseInfo', 'showActivateTab'));
    }

    /**
     * 获取授权信息
     */
    private function getLicenseInfo($configData)
    {
        $result = ['status' => 'unactivated', 'max_bots' => 0, 'expires_at' => null, 'message' => ''];

        // 获取API网站地址配置
        $apiWebConfig = $configData->firstWhere('config_key', 'api_web_url');
        $apiSiteUrl = $apiWebConfig && isset($apiWebConfig->config_val->url) ? $apiWebConfig->config_val->url : null;

        // 获取本地存储的激活码信息（config_val 在模型中可能已被 accessor 转为 object）
        $licenseConfig = $configData->firstWhere('config_key', 'license_activation');
        if (!$licenseConfig) {
            $localLicense = null;
        } else {
            $configVal = $licenseConfig->config_val;
            $localLicense = is_string($configVal) ? json_decode($configVal, true) : (array) $configVal;
        }

        if (!$apiSiteUrl) {
            $result['message'] = '请先配置API网站地址';
            return $result;
        }

        if (!$localLicense || empty($localLicense['auth_code'])) {
            $result['message'] = '请先激活授权';
            return $result;
        }

        // 调用API验证激活状态
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->post($apiSiteUrl . '/api/license/verify', [
                'json' => [
                    'api_base_url' => $apiSiteUrl,
                    'auth_code' => $localLicense['auth_code']
                ]
            ]);

            $apiResult = json_decode($response->getBody()->getContents(), true);

            if ($apiResult['success'] ?? false) {
                $result['status'] = 'active';
                $result['max_bots'] = $apiResult['max_bots'] ?? 0;
                $result['expires_at'] = $apiResult['expires_at'] ?? null;
                $result['message'] = '已激活';
            } else {
                $result['message'] = $apiResult['message'] ?? '授权验证失败';
            }
        } catch (\Exception $e) {
            $result['message'] = 'API连接失败: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * 激活授权
     */
    public function activate(Request $request)
    {
        $apiSiteUrl = $request->input('api_site_url');
        $authCode = $request->input('auth_code');

        if (empty($apiSiteUrl)) {
            return $this->responseData(400, '请填写API网站地址');
        }

        if (empty($authCode)) {
            return $this->responseData(400, '请填写激活码');
        }

        // 规范化 API 地址：去掉末尾斜杠
        $apiSiteUrl = rtrim(trim($apiSiteUrl), '/');

        // 先保存API网站地址配置
        DB::beginTransaction();
        try {
            $admin_id = Auth::guard('admin')->id();
            $time = nowDate();

            // 保存API网站地址
            $apiConfig = SysConfig::where('config_key', 'api_web_url')->first();
            if ($apiConfig) {
                $apiConfig->config_val = json_encode(['url' => $apiSiteUrl]);
                $apiConfig->update_by = (string)$admin_id;
                $apiConfig->update_time = $time;
                $apiConfig->save();
            } else {
                SysConfig::create([
                    'config_key' => 'api_web_url',
                    'config_val' => json_encode(['url' => $apiSiteUrl]),
                    'comments' => 'API连接url',
                    'create_by' => (string)$admin_id,
                    'create_time' => $time,
                    'update_by' => (string)$admin_id,
                    'update_time' => $time,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '保存配置失败: ' . $e->getMessage());
        }

        // 调用API验证激活
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->post($apiSiteUrl . '/api/license/verify', [
                'json' => [
                    'api_base_url' => $apiSiteUrl,
                    'auth_code' => $authCode,
                    'robot_site' => $request->getSchemeAndHttpHost()
                ]
            ]);

            $apiResult = json_decode($response->getBody()->getContents(), true);

            if (($apiResult['success'] ?? false) === true) {
                // 保存激活码信息到本地
                DB::beginTransaction();
                try {
                    $admin_id = Auth::guard('admin')->id();
                    $time = nowDate();

                    $licenseData = json_encode([
                        'auth_code' => $authCode,
                        'max_bots' => $apiResult['max_bots'] ?? 0,
                        'expires_at' => $apiResult['expires_at'] ?? null,
                        'activated_at' => $time,
                    ]);

                    $licenseConfig = SysConfig::where('config_key', 'license_activation')->first();
                    if ($licenseConfig) {
                        $licenseConfig->config_val = $licenseData;
                        $licenseConfig->update_by = (string)$admin_id;
                        $licenseConfig->update_time = $time;
                        $licenseConfig->save();
                    } else {
                        SysConfig::create([
                            'config_key' => 'license_activation',
                            'config_val' => $licenseData,
                            'comments' => '授权激活信息',
                            'create_by' => (string)$admin_id,
                            'create_time' => $time,
                            'update_by' => (string)$admin_id,
                            'update_time' => $time,
                        ]);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->responseData(400, '保存激活信息失败: ' . $e->getMessage());
                }

                return $this->responseData(200, '激活成功');
            } else {
                return $this->responseData(400, $apiResult['message'] ?? '激活失败');
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            if ($status === 404) {
                return $this->responseData(400, '授权接口不存在(404)。请确认填写的是【API授权系统】的网站地址（即部署 API-web 的域名），不要填本机器人后台地址。');
            }
            return $this->responseData(400, 'API连接失败: ' . $e->getMessage());
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '404') !== false) {
                return $this->responseData(400, '授权接口不存在(404)。请确认填写的是【API授权系统】的网站地址（即部署 API-web 的域名），不要填本机器人后台地址。');
            }
            return $this->responseData(400, 'API连接失败: ' . $msg);
        }
    }

    /**
     * 解除授权
     */
    public function deactivate(Request $request)
    {
        try {
            $licenseConfig = SysConfig::where('config_key', 'license_activation')->first();
            if ($licenseConfig) {
                $licenseConfig->delete();
            }
            return $this->responseData(200, '已解除授权');
        } catch (\Exception $e) {
            return $this->responseData(400, '解除授权失败: ' . $e->getMessage());
        }
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
