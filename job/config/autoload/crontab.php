<?php
use Hyperf\Crontab\Crontab;

$jobpre = env('REDIS_PREFIX') ?? 'job_';
return [
    'enable' => boolval(env('APP_CRONTAB',true)),
    // 通过配置文件定义的定时任务
    'crontab' => [
        // 拉取闪兑钱包交易-拉usdt
        (new Crontab())->setName($jobpre.'transitwalletusdttrade')->setRule('*/2 * * * * *')->setCallback([App\Task\GetTransitWalletUsdtTrade::class, 'execute'])->setMemo('拉取闪兑钱包交易Usdt')->setSingleton(true),
        
        // 自动闪兑进货trx
        (new Crontab())->setName($jobpre.'autostocktrx')->setRule('* * * * *')->setCallback([App\Task\AutoStockTRX::class, 'execute'])->setMemo('自动闪兑trx')->setSingleton(true),

        // 闪兑
        (new Crontab())->setName($jobpre.'shanduiBonus')->setRule('*/2 * * * * *')->setCallback([App\Task\HandleShanduiBonus::class, 'execute'])->setMemo('闪兑币')->setSingleton(true),

        // 拉取欧意实时汇率
        (new Crontab())->setName($jobpre.'getoktrealtimerate')->setRule('*/5 * * * * *')->setCallback([App\Task\GetOKTRealtimeRate::class, 'execute'])->setMemo('拉取欧意实时汇率')->setSingleton(true),
        
        // 定时tg消息
        (new Crontab())->setName($jobpre.'sendtimingtgmessage')->setRule('* * * * *')->setCallback([App\Task\SendTimingTgMessage::class, 'execute'])->setMemo('定时tg消息')->setSingleton(true),
        
        // 闪兑通知
        (new Crontab())->setName($jobpre.'sendtransittgmessage')->setRule('*/2 * * * * *')->setCallback([App\Task\SendTransitTgMessage::class, 'execute'])->setMemo('闪兑通知')->setSingleton(true),
        
        // 监听交易-最新的区块V2
        (new Crontab())->setName($jobpre.'monitorWalletBlockv2')->setRule('*/2 * * * * *')->setCallback([App\Task\MonitorWalletBlockV2::class, 'execute'])->setMemo('监听交易v2')->setSingleton(true),
        
        // 监听交易-漏掉的区块V2
        (new Crontab())->setName($jobpre.'monitorWalletBlockLostv2')->setRule('*/2 * * * * *')->setCallback([App\Task\MonitorWalletBlockLostV2::class, 'execute'])->setMemo('监听交易漏掉v2')->setSingleton(true),
        
        // 拉取能量平台余额-并告警
        (new Crontab())->setName($jobpre.'getenergyplatformbalance')->setRule('*/5 * * * * *')->setCallback([App\Task\GetEnergyPlatformBalance::class, 'execute'])->setMemo('拉取能量平台余额')->setSingleton(true),
        
        // 拉取能量钱包交易-拉trx-拉usdt
        (new Crontab())->setName($jobpre.'energywalletinlisttrxusdt')->setRule('*/2 * * * * *')->setCallback([App\Task\GetEnergyWalletTrxUsdtTrade::class, 'execute'])->setMemo('拉取能量钱包交易')->setSingleton(true),
        
        
        // 拉取能量钱包交易-拉trx-拉usdt-通过区块拉取-监控笔数钱包的转出usdt
        (new Crontab())->setName($jobpre.'monitorenergywallettrade')->setRule('*/2 * * * * *')->setCallback([App\Task\MonitorEnergyWalletTrade::class, 'execute'])->setMemo('拉取能量钱包交易区块')->setSingleton(true),
        
        // 拉取能量钱包交易-拉trx-拉usdt-通过区块拉取-监控笔数钱包的转出usdt-漏掉的
        (new Crontab())->setName($jobpre.'monitorenergywallettradelost')->setRule('*/2 * * * * *')->setCallback([App\Task\MonitorEnergyWalletTradeLost::class, 'execute'])->setMemo('拉取能量钱包交易区块漏掉的')->setSingleton(true),
        
        
        // 特殊地址给能量
        (new Crontab())->setName($jobpre.'handleenergyspecial')->setRule('*/3 * * * * *')->setCallback([App\Task\HandleEnergySpecial::class, 'execute'])->setMemo('特殊地址给能量')->setSingleton(true),

        // 能量成功通知
        (new Crontab())->setName($jobpre.'sendenergytgmessage')->setRule('*/2 * * * * *')->setCallback([App\Task\SendEnergyTgMessage::class, 'execute'])->setMemo('能量成功通知')->setSingleton(true),
        
        // 回收能量
        (new Crontab())->setName($jobpre.'recoveryenergy')->setRule('* * * * *')->setCallback([App\Task\RecoveryEnergy::class, 'execute'])->setMemo('回收能量')->setSingleton(true),
        
        // 能量下单
        (new Crontab())->setName($jobpre.'energyOrder')->setRule('*/2 * * * * *')->setCallback([App\Task\HandleEnergyOrder::class, 'execute'])->setMemo('能量下单')->setSingleton(true),
        
        // 开通tg会员
        (new Crontab())->setName($jobpre.'tgpremium')->setRule('*/3 * * * * *')->setCallback([App\Task\HandleTgPremium::class, 'execute'])->setMemo('开通tg会员')->setSingleton(true),
        
        // 取消过期未支付的订单(会员订单和充值订单)
        (new Crontab())->setName($jobpre.'cancelunpaidorder')->setRule('* * * * *')->setCallback([App\Task\CancelUnpaidOrder::class, 'execute'])->setMemo('取消过期未支付的订单')->setSingleton(true),
        
        // 会员成功通知
        (new Crontab())->setName($jobpre.'sendpremiumtgmessage')->setRule('*/2 * * * * *')->setCallback([App\Task\SendPremiumTgMessage::class, 'execute'])->setMemo('会员成功通知')->setSingleton(true),
        
        // 拉取会员钱包交易-拉usdt
        (new Crontab())->setName($jobpre.'premiumwalletusdttrade')->setRule('*/2 * * * * *')->setCallback([App\Task\GetPremiumWalletUsdtTrade::class, 'execute'])->setMemo('拉取会员钱包交易Usdt')->setSingleton(true),
        
        // 拉取充值钱包交易-拉usdt-拉trx
        (new Crontab())->setName($jobpre.'getgmswallettrxusdttrade')->setRule('*/5 * * * * *')->setCallback([App\Task\GetFmsWalletTrxUsdtTrade::class, 'execute'])->setMemo('拉取充值钱包交易')->setSingleton(true),
        
        // 拉取地址资源-智能托管-笔数套餐
        (new Crontab())->setName($jobpre.'getaienergywalletresource')->setRule('*/3 * * * * *')->setCallback([App\Task\GetAiEnergyWalletResource::class, 'execute'])->setMemo('拉取地址资源')->setSingleton(true),
        
        // 智能下单能量-智能托管-笔数套餐
        (new Crontab())->setName($jobpre.'handleaienergyorder')->setRule('*/3 * * * * *')->setCallback([App\Task\HandleAiEnergyOrder::class, 'execute'])->setMemo('智能下单能量')->setSingleton(true),
        
        // 充值订单
        (new Crontab())->setName($jobpre.'handlerechargeorder')->setRule('*/4 * * * * *')->setCallback([App\Task\HandleRechargeOrder::class, 'execute'])->setMemo('充值订单')->setSingleton(true),
        
        // 归集钱包
        (new Crontab())->setName($jobpre.'handlecollectionwallet')->setRule('*/5 * * * *')->setCallback([App\Task\HandleCollectionWallet::class, 'execute'])->setMemo('归集钱包')->setSingleton(true),
        
        // 笔数滞留暂停地址
        (new Crontab())->setName($jobpre.'bishuwalletstop')->setRule('1 3 * * *')->setCallback([App\Task\BishuWalletStop::class, 'execute'])->setMemo('笔数滞留暂停地址')->setSingleton(true),
        
        // 本地开发：轮询 Telegram 消息改为独立 Process 实现，避免 Crontab skipped execution 问题
        // (new Crontab())->setName($jobpre.'polltelegrammessages')->setRule('*/3 * * * * *')->setCallback([App\Task\PollTelegramMessages::class, 'execute'])->setMemo('本地开发：轮询Telegram消息')->setSingleton(true),
    ],
];
