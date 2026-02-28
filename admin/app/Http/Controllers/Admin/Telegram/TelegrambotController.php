<?php

namespace App\Http\Controllers\Admin\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotCommand;
use App\Models\Telegram\TelegramBotKeyreply;
use App\Models\Telegram\TelegramBotKeyreplyKeyboard;
use App\Models\Telegram\TelegramBotAd;
use App\Models\Telegram\TelegramBotAdKeyboard;
use App\Models\Premium\PremiumPlatform;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Transit\TransitWallet;
use Telegram\Bot\Api;

class TelegrambotController extends Controller
{
    /**
     * 新增机器人时自动继承配置的模板机器人 rid（可在 .env 配置 TG_BOT_TEMPLATE_RID）
     */
    private function getBotTemplateRid(): int
    {
        return (int) (env('TG_BOT_TEMPLATE_RID', 1) ?: 1);
    }

    /**
     * 是否在新增机器人时自动复制模板配置（可在 .env 配置 TG_BOT_AUTO_CLONE_CONFIG=true/false）
     */
    private function autoCloneEnabled(): bool
    {
        return (bool) env('TG_BOT_AUTO_CLONE_CONFIG', true);
    }

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

        DB::beginTransaction();
        try {
            /** @var TelegramBot $res */
            $res = TelegramBot::create([
                'bot_token' => $request->bot_token,
                'bot_admin_username' => $request->bot_admin_username,
                'comments' => $request->comments,
                'create_time' => nowDate()
            ]);

            // 新增机器人默认继承模板机器人配置（命令/关键词/广告等），避免“新增机器人不回复”
            if (!empty($res) && $this->autoCloneEnabled()) {
                $fromRid = (int) ($request->clone_from_rid ?? 0);
                if ($fromRid <= 0) {
                    $fromRid = $this->getBotTemplateRid();
                }
                // 防止自己拷贝自己
                if ($fromRid > 0 && (int)$fromRid !== (int)$res->rid) {
                    $this->cloneBotConfigInternal($fromRid, (int)$res->rid, false);
                }
            }

            DB::commit();
            return $this->responseData(200, '添加成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseData(400, '添加失败: ' . $e->getMessage());
        }
    }

    /**
     * 手动复制模板机器人配置到指定机器人（用于已存在的机器人）
     * POST: rid(目标机器人), from_rid(可选，来源机器人，默认 TG_BOT_TEMPLATE_RID), force(可选，1=覆盖)
     */
    public function cloneConfig(Request $request)
    {
        $toRid = (int) ($request->rid ?? 0);
        if ($toRid <= 0) {
            return $this->responseData(400, '缺少目标机器人 rid');
        }

        $fromRid = (int) ($request->from_rid ?? 0);
        if ($fromRid <= 0) {
            $fromRid = $this->getBotTemplateRid();
        }

        if ($fromRid === $toRid) {
            return $this->responseData(400, '来源机器人和目标机器人不能相同');
        }

        $force = (string)($request->force ?? '') === '1';

        DB::beginTransaction();
        try {
            $msg = $this->cloneBotConfigInternal($fromRid, $toRid, $force);
            DB::commit();
            return $this->responseData(200, $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseData(400, '复制失败: ' . $e->getMessage());
        }
    }

    /**
     * 复制机器人配置（命令/关键词/广告 + 关联键盘）
     * @param int $fromRid
     * @param int $toRid
     * @param bool $force 是否覆盖（true 时会先清空目标机器人相关配置）
     * @return string
     */
    private function cloneBotConfigInternal(int $fromRid, int $toRid, bool $force): string
    {
        $fromBot = TelegramBot::where('rid', $fromRid)->first();
        $toBot = TelegramBot::where('rid', $toRid)->first();
        if (empty($fromBot) || empty($toBot)) {
            throw new \RuntimeException('来源或目标机器人不存在');
        }

        if ($force) {
            TelegramBotCommand::where('bot_rid', $toRid)->delete();
            TelegramBotKeyreplyKeyboard::where('bot_rid', $toRid)->delete();
            TelegramBotKeyreply::where('bot_rid', $toRid)->delete();
            TelegramBotAdKeyboard::where('bot_rid', $toRid)->delete();
            TelegramBotAd::where('bot_rid', $toRid)->delete();
        } else {
            // 目标已有配置就不重复复制（避免误覆盖）
            $hasAny = TelegramBotCommand::where('bot_rid', $toRid)->exists()
                || TelegramBotKeyreply::where('bot_rid', $toRid)->exists()
                || TelegramBotAd::where('bot_rid', $toRid)->exists();
            if ($hasAny) {
                return '目标机器人已存在配置，如需覆盖请传 force=1';
            }
        }

        $now = nowDate();
        $copied = [
            'command' => 0,
            'keyreply' => 0,
            'keyreply_keyboard' => 0,
            'ad' => 0,
            'ad_keyboard' => 0,
        ];

        // 1) 复制命令
        $commands = TelegramBotCommand::where('bot_rid', $fromRid)->orderBy('rid')->get();
        foreach ($commands as $c) {
            TelegramBotCommand::create([
                'bot_rid' => $toRid,
                'command' => $c->command,
                'description' => $c->description,
                'command_type' => $c->command_type,
                'seq_sn' => $c->seq_sn,
                'create_by' => $c->create_by,
                'create_time' => $now,
                'update_by' => $c->update_by,
                'update_time' => $now,
            ]);
            $copied['command']++;
        }

        // 2) 复制关键词回复，并建立 rid 映射
        $keyreplyMap = [];
        $keyreplies = TelegramBotKeyreply::where('bot_rid', $fromRid)->orderBy('rid')->get();
        foreach ($keyreplies as $kr) {
            $new = TelegramBotKeyreply::create([
                'bot_rid' => $toRid,
                'key_type' => $kr->key_type,
                'monitor_word' => $kr->monitor_word,
                'reply_photo' => $kr->reply_photo,
                'reply_content' => $kr->reply_content,
                'opt_type' => $kr->opt_type,
                'status' => $kr->status,
                'create_by' => $kr->create_by,
                'create_time' => $now,
                'update_by' => $kr->update_by,
                'update_time' => $now,
            ]);
            $keyreplyMap[(int)$kr->rid] = (int)$new->rid;
            $copied['keyreply']++;
        }

        // 3) 复制关键词-键盘关系（keyboard 表是全局的，不需要复制）
        $krBoards = TelegramBotKeyreplyKeyboard::where('bot_rid', $fromRid)->orderBy('rid')->get();
        foreach ($krBoards as $kb) {
            $oldKeyreplyRid = (int)$kb->keyreply_rid;
            if (!isset($keyreplyMap[$oldKeyreplyRid])) {
                continue;
            }
            TelegramBotKeyreplyKeyboard::create([
                'bot_rid' => $toRid,
                'keyreply_rid' => $keyreplyMap[$oldKeyreplyRid],
                'keyboard_rid' => $kb->keyboard_rid,
                'create_by' => $kb->create_by,
                'create_time' => $now,
                'update_by' => $kb->update_by,
                'update_time' => $now,
            ]);
            $copied['keyreply_keyboard']++;
        }

        // 4) 复制定时广告，并建立 rid 映射
        $adMap = [];
        $ads = TelegramBotAd::where('bot_rid', $fromRid)->orderBy('rid')->get();
        foreach ($ads as $ad) {
            $newAd = TelegramBotAd::create([
                'bot_rid' => $toRid,
                'notice_cycle' => $ad->notice_cycle,
                'notice_obj' => $ad->notice_obj,
                'notice_photo' => $ad->notice_photo,
                'notice_ad' => $ad->notice_ad,
                'status' => $ad->status,
                'last_notice_time' => $ad->last_notice_time,
                'create_by' => $ad->create_by,
                'create_time' => $now,
                'update_by' => $ad->update_by,
                'update_time' => $now,
            ]);
            $adMap[(int)$ad->rid] = (int)$newAd->rid;
            $copied['ad']++;
        }

        // 5) 复制广告-键盘关系
        $adBoards = TelegramBotAdKeyboard::where('bot_rid', $fromRid)->orderBy('rid')->get();
        foreach ($adBoards as $ab) {
            $oldAdRid = (int)$ab->ad_rid;
            if (!isset($adMap[$oldAdRid])) {
                continue;
            }
            TelegramBotAdKeyboard::create([
                'bot_rid' => $toRid,
                'ad_rid' => $adMap[$oldAdRid],
                'keyboard_rid' => $ab->keyboard_rid,
                'create_by' => $ab->create_by,
                'create_time' => $now,
                'update_by' => $ab->update_by,
                'update_time' => $now,
            ]);
            $copied['ad_keyboard']++;
        }

        return '复制完成：命令'.$copied['command'].'条，关键词'.$copied['keyreply'].'条，关键词键盘'.$copied['keyreply_keyboard'].'条，广告'.$copied['ad'].'条，广告键盘'.$copied['ad_keyboard'].'条';
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
