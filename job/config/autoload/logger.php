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

$formatter_array = [
    'class' => Monolog\Formatter\LineFormatter::class,
    'constructor' => [
        'format' => null,
        'dateFormat' => null,
        'allowInlineLineBreaks' => true,
    ],
];
return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf/hyperf.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 记录sql日志
    'sql' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/sql/sql.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],

    // 拉取交易列表
    'getwallet' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/1transitWalletInList/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],

    // 闪兑转账币
    'shanduibonus' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/4shanduibonus/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],

    // 拉取欧意实时汇率
    'getoktrealtimerate' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/9getoktrealtimerate/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 发送tg定时通知
    'sendtimingtgmessage' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/8sendtimingtgmessage/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 闪兑通知
    'sendtransittgmessage' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/7sendtransittgmessage/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 自动进货
    'autostocktrx' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/0autostocktrx/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 监听交易
    'monitorwallet' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/2monitorwallet/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 能量平台更新余额
    'energyplatformbalance' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/3energyplatformbalance/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 拉取能量平台钱包trx交易
    'getenergywallettrxtrade' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/5getenergywallettrxtrade/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 回收能量
    'recoveryenergy' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/5recoveryenergy/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 自动开tg会员
    'tgpremium' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/6tgpremium/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 取消过期为政府的会员订单
    'cancelunpaidorder' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/6cancelunpaidorder/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 拉去会员平台钱包usdt交易
    'getpremiumwalletusdttrade' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/6getpremiumwalletusdttrade/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 拉取充值钱包trx交易
    'getfmswallettrxtrade' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/0getfmswallettrxtrade/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 能量代理
    'handleaienergyorder' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/10handleaienergyorder/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 归集钱包
    'handlecollectionwallet' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/10handlecollectionwallet/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
    // 滞留笔数暂停
    'bishuwalletstop' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/timing/99bishuwalletstop/data.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter_array,
    ],
];
