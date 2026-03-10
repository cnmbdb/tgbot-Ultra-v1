<?php

declare(strict_types=1);

namespace App\Task;

use App\Model\Telegram\TelegramBot;
use App\Library\Log;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;

/**
 * 本地开发模式：使用长轮询获取 Telegram 消息
 * 注意：此任务仅在本地开发环境使用，生产环境应使用 Webhook
 */
class PollTelegramMessages
{
    public function execute()
    {
        // 添加执行时间限制，避免任务卡住导致被跳过（本地开发适当放宽）
        set_time_limit(5);
        
        try {
            // 使用 error_log 直接输出，确保能看到日志
            error_log("PollTelegramMessages: 任务开始执行");
            
            // 获取所有机器人
            $bots = TelegramBot::where('bot_token', '<>', '')->get();
        
            if ($bots->isEmpty()) {
                error_log("PollTelegramMessages: 没有找到机器人配置");
                return;
            }
            
            error_log("PollTelegramMessages: 找到 " . $bots->count() . " 个机器人");
        
        foreach ($bots as $bot) {
            try {
                // 使用 Telegram HTTP 接口，避免 SDK 与 Hyperf Collection 类型冲突
                $baseUrl = 'https://api.telegram.org/bot' . $bot->bot_token . '/';

                // 本地开发：尝试删除 Webhook（如果线上配置过 Webhook，getUpdates 会无效）
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $baseUrl . 'deleteWebhook');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                    curl_exec($ch);
                    curl_close($ch);
                } catch (\Throwable $e) {
                    // 忽略错误，继续
                }

                // 获取最后处理的 update_id（使用数据库，避免 Redis 连接问题）
                $lastUpdateId = 0;
                try {
                    $lastUpdateId = (int)Db::table('t_telegram_bot')
                        ->where('rid', $bot->rid)
                        ->value('last_update_id') ?: 0;
                } catch (\Exception $e) {
                    // 如果字段不存在，使用 0
                }
                
                // 使用 getUpdates 获取新消息（直接通过 HTTP 调用）
                try {
                    error_log("PollTelegramMessages: 开始获取消息 [{$bot->rid}], last_update_id: {$lastUpdateId}");

                    $params = http_build_query([
                        'offset' => $lastUpdateId + 1,
                        // 本地开发：降低超时时间，加快返回速度（配合进程 0.5s 间隔）
                        'timeout' => 0,
                        'limit' => 100,
                    ]);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $baseUrl . 'getUpdates?' . $params);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    if ($httpCode !== 200 || empty($response)) {
                        error_log("PollTelegramMessages: getUpdates HTTP错误 [{$bot->rid}] code={$httpCode}, error={$curlError}");
                        continue;
                    }

                    $data = json_decode($response, true);
                    if (!($data['ok'] ?? false)) {
                        error_log("PollTelegramMessages: getUpdates 返回错误 [{$bot->rid}]: " . substr($response, 0, 500));
                        continue;
                    }

                    $updates = $data['result'] ?? [];
                    if (empty($updates)) {
                        error_log("PollTelegramMessages: 没有新消息 [{$bot->rid}]");
                        continue;
                    }

                    error_log("PollTelegramMessages: 收到 " . count($updates) . " 条新消息 [bot_rid: {$bot->rid}]");
                } catch (\Throwable $e) {
                    error_log("PollTelegramMessages: getUpdates 失败 [{$bot->rid}]: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine());
                    continue;
                }
                
                // 处理每个更新（此处 $updates 为数组）
                foreach ($updates as $update) {
                    try {
                        // $update 是数组，直接读取 update_id
                        $updateId = $update['update_id'] ?? 0;
                        $updateData = $update;
                        
                        $this->processUpdate($bot->rid, $updateData);
                        
                        // 更新最后处理的 update_id（保存到数据库）
                        try {
                            Db::table('t_telegram_bot')
                                ->where('rid', $bot->rid)
                                ->update(['last_update_id' => $updateId]);
                        } catch (\Exception $e) {
                            // 如果字段不存在，尝试添加字段（仅第一次）
                            try {
                                Db::statement("ALTER TABLE t_telegram_bot ADD COLUMN IF NOT EXISTS last_update_id BIGINT DEFAULT 0");
                                Db::table('t_telegram_bot')
                                    ->where('rid', $bot->rid)
                                    ->update(['last_update_id' => $updateId]);
                            } catch (\Exception $e2) {
                                // 忽略
                            }
                        }
                    } catch (\Exception $e) {
                        error_log("处理单个更新失败 [{$bot->rid}]: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine());
                    }
                }
            } catch (\Exception $e) {
                $err = "轮询机器人消息失败 [{$bot->rid}]: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine();
                error_log($err);
            }
        }
        } catch (\Throwable $e) {
            $msg = "PollTelegramMessages 任务失败: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine();
            error_log($msg);
            throw $e; // 重新抛出异常，让 Hyperf 记录
        }
    }
    
    /**
     * 处理更新，模拟 Webhook 请求
     */
    private function processUpdate($botRid, $updateData)
    {
        // 构建请求数据，模拟 Webhook 请求
        $requestData = [
            'rid' => $botRid,
        ];
        
        // 合并 update 数据
        if (isset($updateData['message'])) {
            $requestData['message'] = $updateData['message'];
        }
        if (isset($updateData['callback_query'])) {
            $requestData['callback_query'] = $updateData['callback_query'];
        }
        if (isset($updateData['my_chat_member'])) {
            $requestData['my_chat_member'] = $updateData['my_chat_member'];
        }
        if (isset($updateData['chat_join_request'])) {
            $requestData['chat_join_request'] = $updateData['chat_join_request'];
        }
        
        // 调用 getdata 方法处理消息（Docker Compose 使用 service 名称作为 hostname）
        // 注意：这里需要直接调用控制器方法，或者通过 HTTP 请求
        // 为了简化，我们直接调用处理逻辑
        try {
            // 通过 HTTP 请求调用 getdata 接口
            // 使用 admin 容器的内部地址
            $url = 'http://admin/api/telegram/getdata?rid=' . $botRid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 减少总超时时间，提升响应速度
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // 减少连接超时时间
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            error_log("PollTelegramMessages: 调用 getdata [bot_rid: {$botRid}, url: {$url}, http_code: {$httpCode}]");
            
            if ($httpCode !== 200) {
                $respPreview = is_string($response) ? substr($response, 0, 500) : json_encode($response);
                error_log("处理消息返回非200状态码: {$httpCode}, 响应: " . $respPreview);
            }
        } catch (\Exception $e) {
            error_log("处理消息失败: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
