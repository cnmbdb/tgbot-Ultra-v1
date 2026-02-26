<?php

declare(strict_types=1);

namespace App\Process;

use App\Task\PollTelegramMessages;
use Hyperf\Process\AbstractProcess;

/**
 * 独立进程：本地开发轮询 Telegram 消息
 * 避免依赖 Crontab 调度导致 skipped execution 问题
 */
class TelegramPollingProcess extends AbstractProcess
{
    /**
     * 进程名称
     * @var string
     */
    public $name = 'telegram-polling-process';

    /**
     * 进程数量
     * @var int
     */
    public $nums = 1;

    /**
     * 是否启用协程
     * @var bool
     */
    public $enableCoroutine = true;

    public function handle(): void
    {
        $task = new PollTelegramMessages();

        while (true) {
            try {
                $task->execute();
            } catch (\Throwable $e) {
                error_log('TelegramPollingProcess error: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            }

            // 本地开发：缩短轮询间隔，加快响应速度（约 0.5 秒一轮）
            usleep(500000); // 500ms
        }
    }
}

