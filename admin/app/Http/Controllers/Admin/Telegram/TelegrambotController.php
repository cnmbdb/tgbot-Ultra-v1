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
        
        // 构建 webhook URL
        // 优先使用环境变量 WEBHOOK_BASE_URL
        $webhookBaseUrl = env('WEBHOOK_BASE_URL');
        
        if(empty($webhookBaseUrl)){
            // 如果没有配置 WEBHOOK_BASE_URL，尝试从请求中获取
            // 优先使用 X-Forwarded-Host（反向代理环境）
            $host = $request->header('X-Forwarded-Host');
            if(empty($host)){
                $host = $request->getHost();
            }
            
            // 检测协议：优先使用 X-Forwarded-Proto（反向代理环境）
            $scheme = $request->header('X-Forwarded-Proto');
            if(empty($scheme)){
                $scheme = $request->isSecure() ? 'https' : 'http';
            }
            
            // 检查端口（从 X-Forwarded-Port 或 HTTP_HOST 中获取）
            $port = $request->header('X-Forwarded-Port');
            if(empty($port)){
                // 从 HTTP_HOST 中解析端口
                $hostWithPort = $request->getHttpHost();
                if(strpos($hostWithPort, ':') !== false){
                    $parts = explode(':', $hostWithPort);
                    $port = isset($parts[1]) ? (int)$parts[1] : null;
                } else {
                    // 如果没有端口，使用默认端口
                    $port = ($scheme === 'https') ? 443 : 80;
                }
            } else {
                $port = (int)$port;
            }
            
            // 检查是否为本地开发环境
            $isLocalDev = (
                $port == 8080 || 
                $host == 'localhost' || 
                strpos($host, 'localhost') !== false ||
                strpos($host, '127.0.0.1') !== false ||
                strpos($host, '192.168.') !== false ||
                strpos($host, '10.') !== false ||
                env('APP_ENV') === 'local' ||
                env('APP_ENV') === 'development'
            );
            
            // 检查端口是否支持
            if($port && !in_array($port, [80, 88, 443, 8443])){
                if($isLocalDev){
                    return $this->responseData(400, '本地开发环境无法设置 Webhook。Telegram Webhook 只支持端口 80, 88, 443, 8443，且需要公网可访问的地址。请在生产环境（使用标准端口）部署后再设置 Webhook。');
                }
                return $this->responseData(400, 'Webhook 端口错误: Telegram 只支持端口 80, 88, 443, 8443。当前端口: ' . $port . '。请在 .env 文件中配置 WEBHOOK_BASE_URL 为使用标准端口的公网地址。');
            }
            
            // 构建 webhook URL（标准端口不需要在URL中显示）
            if(($scheme === 'https' && $port == 443) || ($scheme === 'http' && $port == 80)){
                $webhookBaseUrl = $scheme . '://' . $host;
            } else {
                $webhookBaseUrl = $scheme . '://' . $host . ':' . $port;
            }
        }
        
        // 确保 URL 以 / 结尾（如果没有）
        $webhookBaseUrl = rtrim($webhookBaseUrl, '/');
        $domain = $webhookBaseUrl . '/api/telegram/getdata?rid=' . $request->rid;
        $url = 'https://api.telegram.org/bot'. $data->bot_token .'/setWebhook?url='. urlencode($domain);
        
        try {
            $http = new AipHttpClient();
            $result = $http->get($url);
            // llog($result,'single');
        
            if($result['code'] != 200){
                // 尝试解析错误信息
                $errorMsg = '请求失败,需要外网权限';
                if(isset($result['content'])){
                    $errorContent = json_decode($result['content'], true);
                    if(isset($errorContent['description'])){
                        $errorMsg = '请求失败: ' . $errorContent['description'];
                    }
                }
                return $this->responseData(400, $errorMsg);
            }
            
            $res = json_decode($result['content'],true);

            if($res['ok'] && $res['result']){
                $desc = $res['description'] ?? 'Webhook设置成功';
                return $this->responseData(200, $desc);
            }
            
            // 如果返回了错误信息，显示具体错误
            $errorDesc = isset($res['description']) ? $res['description'] : 'webhook设置失败';
            return $this->responseData(400, $errorDesc);
        } catch (\Exception $e) {
            return $this->responseData(400, '请求异常: ' . $e->getMessage());
        }
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
