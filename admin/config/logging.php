<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
            'permission' => '0666'
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'permission' => '0755'
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel/laravel.log'),
            'level' => 'debug',
            'days' => 300,
            'permission' => '0755'
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
            'permission' => '0666'
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
            'permission' => '0666'
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'permission' => '0666'
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
            'permission' => '0666'
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
            'permission' => '0666'
        ],
        'jobs' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jobs/job.log'),
            'level' => 'debug',
            'days' => 300,
            'permission' => '0666'
        ],
        'event' => [
            'driver' => 'daily',
            'path' => storage_path('logs/events/event.log'),
            'level' => 'debug',
            'days' => 300,
            'permission' => '0666'
        ],
        'sql' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql/sql.log'),
            'level' => 'debug',
            'days' => 5,
            'permission' => '0666'
        ],
        'modify' => [
            'driver' => 'daily',
            'path' => storage_path('logs/modify/modify.log'),
            'level' => 'debug',
            'days' => 300,
            'permission' => '0666'
        ],
    ],
];
