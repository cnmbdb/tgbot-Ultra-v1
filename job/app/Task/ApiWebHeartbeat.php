<?php
namespace App\Task;

class ApiWebHeartbeat
{
    public function execute()
    {
        try {
            send_api_web_heartbeat();
        } catch (\Throwable $e) {
            // 心跳失败不影响主任务
        }
    }
}
