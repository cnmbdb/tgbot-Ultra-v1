<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => env('DB_DRIVER', 'pgsql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'db'),
        'port' => env('DB_PORT', 5432),
        'username' => env('DB_USERNAME', 'user'),
        'password' => env('DB_PASSWORD', 'pwd'),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_general_ci'),
        'prefix' => env('DB_PREFIX', 't_'),
        'pool' => [
            'min_connections' => 2, // 最小连接数增加到2，确保有备用连接
            'max_connections' => 400,
            'connect_timeout' => 10.0,
            'wait_timeout' => 10.0, // 增加等待超时时间
            'heartbeat' => 30, // 启用心跳检测，每30秒检查一次连接
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 300), // 增加最大空闲时间到5分钟
        ],
        // 连接重试配置
        'retry' => [
            'max_retries' => (int) env('DB_MAX_RETRIES', 3), // 最大重试次数
            'retry_interval' => (float) env('DB_RETRY_INTERVAL', 5.0), // 重试间隔（秒）
        ],
        // PostgreSQL 连接选项
        'options' => [
            // 连接超时
            \PDO::ATTR_TIMEOUT => 10,
            // 错误模式：抛出异常
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            // 自动提交
            \PDO::ATTR_AUTOCOMMIT => true,
            // 不使用模拟预处理
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
            ],
        ],
    ],
];
