<?php
/**
 * 管理后台路由
 */

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    // Route::group(['namespace' => 'v1.0.1'], function(){
    //后台登录
    Route::group(['namespace' => 'Auth', 'middleware'=>'XSS'], function () {
        //登录
        Route::get('login', 'LoginController@showLoginForm')->name('admin.login');
        Route::post('login', 'LoginController@login')->name('admin.login');
        //登出
        Route::post('logout', 'LoginController@logout')->name('admin.logout');
    });

    Route::group(['middleware' => ['admin.auth', 'permission']], function () {
        //后台首页
        Route::get('home', 'Home\HomeController@index')->name('admin.home');
        
        // 机器人管理
        Route::group(['namespace' => 'Telegram', 'prefix' => 'telegram'], function () {
            // 机器人列表
            Route::group(['prefix' => 'telegrambot', 'middleware'=>'XSS'], function () {
                Route::get('get_data', 'TelegrambotController@getData')->name('admin.telegram.telegrambot.get_data');
                Route::get('index', 'TelegrambotController@index')->name('admin.telegram.telegrambot.index');
                Route::post('add', 'TelegrambotController@add')->name('admin.telegram.telegrambot.add');
                Route::post('update', 'TelegrambotController@update')->name('admin.telegram.telegrambot.update');
                Route::post('delete', 'TelegrambotController@delete')->name('admin.telegram.telegrambot.delete');
                Route::post('gengxin', 'TelegrambotController@gengxin')->name('admin.telegram.telegrambot.gengxin');
                Route::post('regwebhook', 'TelegrambotController@regwebhook')->name('admin.telegram.telegrambot.regwebhook');
                Route::post('recharge', 'TelegrambotController@recharge')->name('admin.telegram.telegrambot.recharge');
            });
            
            // 机器人关键字回复设置
            Route::group(['prefix' => 'keyreply'], function () {
                Route::get('get_data', 'KeyreplyController@getData')->name('admin.telegram.keyreply.get_data');
                Route::get('index', 'KeyreplyController@index')->name('admin.telegram.keyreply.index');
                Route::post('add', 'KeyreplyController@add')->name('admin.telegram.keyreply.add');
                Route::post('update', 'KeyreplyController@update')->name('admin.telegram.keyreply.update');
                Route::post('delete', 'KeyreplyController@delete')->name('admin.telegram.keyreply.delete');
                Route::post('change_status', 'KeyreplyController@change_status')->name('admin.telegram.keyreply.change_status');
                Route::get('show', 'KeyreplyController@show')->name('admin.telegram.keyreply.show');
                Route::post('copy_paste', 'KeyreplyController@copyPaste')->name('admin.telegram.keyreply.copy_paste');
            });
            
            // 机器人键盘设置
            Route::group(['prefix' => 'keyboard', 'middleware'=>'XSS'], function () {
                Route::get('get_data', 'KeyboardController@getData')->name('admin.telegram.keyboard.get_data');
                Route::get('index', 'KeyboardController@index')->name('admin.telegram.keyboard.index');
                Route::post('add', 'KeyboardController@add')->name('admin.telegram.keyboard.add');
                Route::post('update', 'KeyboardController@update')->name('admin.telegram.keyboard.update');
                Route::post('delete', 'KeyboardController@delete')->name('admin.telegram.keyboard.delete');
                Route::post('change_status', 'KeyboardController@change_status')->name('admin.telegram.keyboard.change_status');
            });
            
            // 机器人命令设置
            Route::group(['prefix' => 'command', 'middleware'=>'XSS'], function () {
                Route::get('get_data', 'CommandController@getData')->name('admin.telegram.command.get_data');
                Route::get('index', 'CommandController@index')->name('admin.telegram.command.index');
                Route::post('add', 'CommandController@add')->name('admin.telegram.command.add');
                Route::post('update', 'CommandController@update')->name('admin.telegram.command.update');
                Route::post('delete', 'CommandController@delete')->name('admin.telegram.command.delete');
                Route::post('sync', 'CommandController@sync')->name('admin.telegram.command.sync');
                Route::post('copy_paste', 'CommandController@copyPaste')->name('admin.telegram.command.copy_paste');
            });
            
            // 机器人关键字键盘设置
            Route::group(['prefix' => 'keyreplyboard', 'middleware'=>'XSS'], function () {
                Route::get('get_data', 'KeyreplyboardController@getData')->name('admin.telegram.keyreplyboard.get_data');
                Route::get('index', 'KeyreplyboardController@index')->name('admin.telegram.keyreplyboard.index');
                Route::post('add', 'KeyreplyboardController@add')->name('admin.telegram.keyreplyboard.add');
                Route::post('update', 'KeyreplyboardController@update')->name('admin.telegram.keyreplyboard.update');
                Route::post('delete', 'KeyreplyboardController@delete')->name('admin.telegram.keyreplyboard.delete');
                Route::post('fastadd', 'KeyreplyboardController@fastadd')->name('admin.telegram.keyreplyboard.fastadd');
                Route::post('fastbotadd', 'KeyreplyboardController@fastbotadd')->name('admin.telegram.keyreplyboard.fastbotadd');
                Route::post('fastbotdelete', 'KeyreplyboardController@fastbotdelete')->name('admin.telegram.keyreplyboard.fastbotdelete');
            });
            
            // 定时广告
            Route::group(['prefix' => 'telegrambotad'], function () {
                Route::get('get_data', 'TelegramBotAdController@getData')->name('admin.telegram.telegrambotad.get_data');
                Route::get('index', 'TelegramBotAdController@index')->name('admin.telegram.telegrambotad.index');
                Route::post('add', 'TelegramBotAdController@add')->name('admin.telegram.telegrambotad.add');
                Route::post('update', 'TelegramBotAdController@update')->name('admin.telegram.telegrambotad.update');
                Route::post('delete', 'TelegramBotAdController@delete')->name('admin.telegram.telegrambotad.delete');
                Route::post('change_status', 'TelegramBotAdController@change_status')->name('admin.telegram.telegrambotad.change_status');
                Route::get('show', 'TelegramBotAdController@show')->name('admin.telegram.telegrambotad.show');
                Route::post('copy_paste', 'TelegramBotAdController@copyPaste')->name('admin.telegram.telegrambotad.copy_paste');
            });
            
            // 定时广告字键盘设置
            Route::group(['prefix' => 'telegrambotadkeyboard', 'middleware'=>'XSS'], function () {
                Route::get('get_data', 'TelegramBotAdKeyboardController@getData')->name('admin.telegram.telegrambotadkeyboard.get_data');
                Route::get('index', 'TelegramBotAdKeyboardController@index')->name('admin.telegram.telegrambotadkeyboard.index');
                Route::post('add', 'TelegramBotAdKeyboardController@add')->name('admin.telegram.telegrambotadkeyboard.add');
                Route::post('update', 'TelegramBotAdKeyboardController@update')->name('admin.telegram.telegrambotadkeyboard.update');
                Route::post('delete', 'TelegramBotAdKeyboardController@delete')->name('admin.telegram.telegrambotadkeyboard.delete');
                Route::post('fastadd', 'TelegramBotAdKeyboardController@fastadd')->name('admin.telegram.telegrambotadkeyboard.fastadd');
            });
        });
        
        // 群组用户
        Route::group(['namespace' => 'Groupuser', 'prefix' => 'groupuser', 'middleware'=>'XSS'], function () {
            Route::group(['prefix' => 'group'], function () {
                Route::get('get_data', 'GroupController@getData')->name('admin.groupuser.group.get_data');
                Route::get('index', 'GroupController@index')->name('admin.groupuser.group.index');
                Route::post('sendmessage', 'GroupController@sendmessage')->name('admin.groupuser.group.sendmessage');
                Route::post('delete', 'GroupController@delete')->name('admin.groupuser.group.delete');
            });
            Route::group(['prefix' => 'user'], function () {
                Route::get('get_data', 'UserController@getData')->name('admin.groupuser.user.get_data');
                Route::get('index', 'UserController@index')->name('admin.groupuser.user.index');
                Route::post('sendmessage', 'UserController@sendmessage')->name('admin.groupuser.user.sendmessage');
                Route::post('batchsendmessage', 'UserController@batchsendmessage')->name('admin.groupuser.user.batchsendmessage');
                Route::post('rechargemanual', 'UserController@rechargemanual')->name('admin.groupuser.user.rechargemanual');
                Route::post('delete', 'UserController@delete')->name('admin.groupuser.user.delete');
                Route::post('batchdelete', 'UserController@batchdelete')->name('admin.groupuser.user.batchdelete');
            });
            // 充值订单
            Route::group(['prefix' => 'rechargeorder'], function () {
                Route::get('get_data', 'FmsRechargeOrderController@getData')->name('admin.groupuser.rechargeorder.get_data');
                Route::get('index', 'FmsRechargeOrderController@index')->name('admin.groupuser.rechargeorder.index');
            });
            // 充值交易
            Route::group(['prefix' => 'rechargetrade'], function () {
                Route::get('get_data', 'FmsWalletTradeController@getData')->name('admin.groupuser.rechargetrade.get_data');
                Route::get('index', 'FmsWalletTradeController@index')->name('admin.groupuser.rechargetrade.index');
                Route::post('stoporder', 'FmsWalletTradeController@stoporder')->name('admin.groupuser.rechargetrade.stoporder');
            });
        });
            
        // 闪兑管理
        Route::group(['namespace' => 'Transit', 'prefix' => 'transit', 'middleware'=>'XSS'], function () {
            // 闪兑钱包
            Route::group(['prefix' => 'wallet'], function () {
                Route::get('get_data', 'TransitWalletController@getData')->name('admin.transit.wallet.get_data');
                Route::get('index', 'TransitWalletController@index')->name('admin.transit.wallet.index');
                Route::post('add', 'TransitWalletController@add')->name('admin.transit.wallet.add');
                Route::post('update', 'TransitWalletController@update')->name('admin.transit.wallet.update');
                Route::post('delete', 'TransitWalletController@delete')->name('admin.transit.wallet.delete');
                Route::post('change_status', 'TransitWalletController@change_status')->name('admin.transit.wallet.change_status');
                Route::post('updateprikey', 'TransitWalletController@updateprikey')->name('admin.transit.wallet.updateprikey');
                Route::post('approve', 'TransitWalletController@approve')->name('admin.transit.wallet.approve');
                Route::post('manualtrx', 'TransitWalletController@manualtrx')->name('admin.transit.wallet.manualtrx');
            });
            
            // 闪兑币种
            Route::group(['prefix' => 'walletcoin'], function () {
                Route::get('get_data', 'TransitWalletCoinController@getData')->name('admin.transit.walletcoin.get_data');
                Route::get('index', 'TransitWalletCoinController@index')->name('admin.transit.walletcoin.index');
                Route::post('add', 'TransitWalletCoinController@add')->name('admin.transit.walletcoin.add');
                Route::post('update', 'TransitWalletCoinController@update')->name('admin.transit.walletcoin.update');
                Route::post('delete', 'TransitWalletCoinController@delete')->name('admin.transit.walletcoin.delete');
            });
            
            // 闪兑交易
            Route::group(['prefix' => 'trade'], function () {
                Route::get('get_data', 'TransitTradeController@getData')->name('admin.transit.trade.get_data');
                Route::get('index', 'TransitTradeController@index')->name('admin.transit.trade.index');
                Route::post('reswap', 'TransitTradeController@reswap')->name('admin.transit.trade.reswap');
                Route::post('stopswap', 'TransitTradeController@stopswap')->name('admin.transit.trade.stopswap');
            });
            
            // 闪兑黑钱包
            Route::group(['prefix' => 'walletblack'], function () {
                Route::get('get_data', 'TransitWalletblackController@getData')->name('admin.transit.walletblack.get_data');
                Route::get('index', 'TransitWalletblackController@index')->name('admin.transit.walletblack.index');
                Route::post('add', 'TransitWalletblackController@add')->name('admin.transit.walletblack.add');
                Route::post('update', 'TransitWalletblackController@update')->name('admin.transit.walletblack.update');
                Route::post('delete', 'TransitWalletblackController@delete')->name('admin.transit.walletblack.delete');
            });
            
            // 闪兑用户
            Route::group(['prefix' => 'userwallet'], function () {
                Route::get('get_data', 'TransitUserWalletController@getData')->name('admin.transit.userwallet.get_data');
                Route::get('index', 'TransitUserWalletController@index')->name('admin.transit.userwallet.index');
                Route::post('add', 'TransitUserWalletController@add')->name('admin.transit.userwallet.add');
                Route::post('update', 'TransitUserWalletController@update')->name('admin.transit.userwallet.update');
            });
        });
        
        // 能量管理
        Route::group(['namespace' => 'Energy', 'prefix' => 'energy', 'middleware'=>'XSS'], function () {
            // 能量平台轮询
            Route::group(['prefix' => 'platform'], function () {
                Route::get('get_data', 'EnergyPlatformController@getData')->name('admin.energy.platform.get_data');
                Route::get('index', 'EnergyPlatformController@index')->name('admin.energy.platform.index');
                Route::post('add', 'EnergyPlatformController@add')->name('admin.energy.platform.add');
                Route::post('update', 'EnergyPlatformController@update')->name('admin.energy.platform.update');
                Route::post('delete', 'EnergyPlatformController@delete')->name('admin.energy.platform.delete');
                Route::post('change_status', 'EnergyPlatformController@change_status')->name('admin.energy.platform.change_status');
                Route::post('updateapikey', 'EnergyPlatformController@updateapikey')->name('admin.energy.platform.updateapikey');
                // NL-API 平台余额充值下单
                Route::post('nlapi-recharge', 'EnergyPlatformController@nlApiRecharge')->name('admin.energy.platform.nlapi_recharge');
                Route::get('nlapi-recharge-history', 'EnergyPlatformController@nlapiRechargeHistory')->name('admin.energy.platform.nlapi_recharge_history');
            });
            // 机器人能量
            Route::group(['prefix' => 'platformbot'], function () {
                Route::get('get_data', 'EnergyPlatformBotController@getData')->name('admin.energy.platformbot.get_data');
                Route::get('index', 'EnergyPlatformBotController@index')->name('admin.energy.platformbot.index');
                Route::post('add', 'EnergyPlatformBotController@add')->name('admin.energy.platformbot.add');
                Route::post('update', 'EnergyPlatformBotController@update')->name('admin.energy.platformbot.update');
                Route::post('delete', 'EnergyPlatformBotController@delete')->name('admin.energy.platformbot.delete');
                Route::post('change_status', 'EnergyPlatformBotController@change_status')->name('admin.energy.platformbot.change_status');
                Route::post('aitrusteeship', 'EnergyPlatformBotController@aitrusteeship')->name('admin.energy.platformbot.aitrusteeship');
                Route::post('bishu', 'EnergyPlatformBotController@bishu')->name('admin.energy.platformbot.bishu');
            });
            // 能量套餐
            Route::group(['prefix' => 'package'], function () {
                Route::get('get_data', 'EnergyPlatformPackageController@getData')->name('admin.energy.package.get_data');
                Route::get('index', 'EnergyPlatformPackageController@index')->name('admin.energy.package.index');
                Route::post('add', 'EnergyPlatformPackageController@add')->name('admin.energy.package.add');
                Route::post('update', 'EnergyPlatformPackageController@update')->name('admin.energy.package.update');
                Route::post('delete', 'EnergyPlatformPackageController@delete')->name('admin.energy.package.delete');
                Route::post('change_status', 'EnergyPlatformPackageController@change_status')->name('admin.energy.package.change_status');
                Route::get('show', 'EnergyPlatformPackageController@show')->name('admin.energy.package.show');
                Route::post('copy_paste', 'EnergyPlatformPackageController@copyPaste')->name('admin.energy.package.copy_paste');
                Route::post('batchdelete', 'EnergyPlatformPackageController@batchdelete')->name('admin.energy.package.batchdelete');
            });
            // 能量订单
            Route::group(['prefix' => 'order'], function () {
                Route::get('get_data', 'EnergyPlatformOrderController@getData')->name('admin.energy.order.get_data');
                Route::get('index', 'EnergyPlatformOrderController@index')->name('admin.energy.order.index');
                Route::post('batch_recovery_energy', 'EnergyPlatformOrderController@batchRecoveryEnergy')->name('admin.energy.order.batch_recovery_energy');
                Route::post('alreadyrecover', 'EnergyPlatformOrderController@alreadyrecover')->name('admin.energy.order.alreadyrecover');
            });
            // 能量交易
            Route::group(['prefix' => 'trade'], function () {
                Route::get('get_data', 'EnergyWalletTradeController@getData')->name('admin.energy.trade.get_data');
                Route::get('index', 'EnergyWalletTradeController@index')->name('admin.energy.trade.index');
                Route::post('reorder', 'EnergyWalletTradeController@reorder')->name('admin.energy.trade.reorder');
            });
            // 快捷订单
            Route::group(['prefix' => 'quick'], function () {
                Route::get('get_data', 'EnergyQuickOrderController@getData')->name('admin.energy.quick.get_data');
                Route::get('index', 'EnergyQuickOrderController@index')->name('admin.energy.quick.index');
                Route::post('reorder', 'EnergyQuickOrderController@reorder')->name('admin.energy.quick.reorder');
            });
            // 智能托管
            Route::group(['prefix' => 'aitrusteeship'], function () {
                Route::get('get_data', 'EnergyAiTrusteeshipController@getData')->name('admin.energy.aitrusteeship.get_data');
                Route::get('index', 'EnergyAiTrusteeshipController@index')->name('admin.energy.aitrusteeship.index');
                Route::post('update', 'EnergyAiTrusteeshipController@update')->name('admin.energy.aitrusteeship.update');
                Route::post('delete', 'EnergyAiTrusteeshipController@delete')->name('admin.energy.aitrusteeship.delete');
                Route::post('refresh', 'EnergyAiTrusteeshipController@refresh')->name('admin.energy.aitrusteeship.refresh');
            });
            // 笔数能量
            Route::group(['prefix' => 'aibishu'], function () {
                Route::get('get_data', 'EnergyAiBishuController@getData')->name('admin.energy.aibishu.get_data');
                Route::get('index', 'EnergyAiBishuController@index')->name('admin.energy.aibishu.index');
                Route::post('update', 'EnergyAiBishuController@update')->name('admin.energy.aibishu.update');
                Route::post('delete', 'EnergyAiBishuController@delete')->name('admin.energy.aibishu.delete');
                Route::post('refresh', 'EnergyAiBishuController@refresh')->name('admin.energy.aibishu.refresh');
                Route::post('add', 'EnergyAiBishuController@add')->name('admin.energy.aibishu.add');
            });
        });
        
        // 会员管理
        Route::group(['namespace' => 'Premium', 'prefix' => 'premium', 'middleware'=>'XSS'], function () {
            // 会员平台
            Route::group(['prefix' => 'platform'], function () {
                Route::get('get_data', 'PremiumPlatformController@getData')->name('admin.premium.platform.get_data');
                Route::get('index', 'PremiumPlatformController@index')->name('admin.premium.platform.index');
                Route::post('add', 'PremiumPlatformController@add')->name('admin.premium.platform.add');
                Route::post('update', 'PremiumPlatformController@update')->name('admin.premium.platform.update');
                Route::post('delete', 'PremiumPlatformController@delete')->name('admin.premium.platform.delete');
                Route::post('change_status', 'PremiumPlatformController@change_status')->name('admin.premium.platform.change_status');
                Route::post('updateapikey', 'PremiumPlatformController@updateapikey')->name('admin.premium.platform.updateapikey');
                Route::post('updatephrase', 'PremiumPlatformController@updatephrase')->name('admin.premium.platform.updatephrase');
            });
            // 会员套餐
            Route::group(['prefix' => 'package'], function () {
                Route::get('get_data', 'PremiumPlatformPackageController@getData')->name('admin.premium.package.get_data');
                Route::get('index', 'PremiumPlatformPackageController@index')->name('admin.premium.package.index');
                Route::post('add', 'PremiumPlatformPackageController@add')->name('admin.premium.package.add');
                Route::post('update', 'PremiumPlatformPackageController@update')->name('admin.premium.package.update');
                Route::post('delete', 'PremiumPlatformPackageController@delete')->name('admin.premium.package.delete');
                Route::post('change_status', 'PremiumPlatformPackageController@change_status')->name('admin.premium.package.change_status');
                Route::get('show', 'PremiumPlatformPackageController@show')->name('admin.premium.package.show');
                Route::post('copy_paste', 'PremiumPlatformPackageController@copyPaste')->name('admin.premium.package.copy_paste');
            });
            // 会员订单
            Route::group(['prefix' => 'order'], function () {
                Route::get('get_data', 'PremiumPlatformOrderController@getData')->name('admin.premium.order.get_data');
                Route::get('index', 'PremiumPlatformOrderController@index')->name('admin.premium.order.index');
            });
            // 会员交易
            Route::group(['prefix' => 'trade'], function () {
                Route::get('get_data', 'PremiumWalletTradeController@getData')->name('admin.premium.trade.get_data');
                Route::get('index', 'PremiumWalletTradeController@index')->name('admin.premium.trade.index');
                Route::post('reorder', 'PremiumWalletTradeController@reorder')->name('admin.premium.trade.reorder');
                Route::post('stoporder', 'PremiumWalletTradeController@stoporder')->name('admin.premium.trade.stoporder');
            });
        });
        
        // 监控管理
        Route::group(['namespace' => 'Monitor', 'prefix' => 'monitor', 'middleware'=>'XSS'], function () {
            // 机器人监控
            Route::group(['prefix' => 'bot'], function () {
                Route::get('get_data', 'MonitorBotController@getData')->name('admin.monitor.bot.get_data');
                Route::get('index', 'MonitorBotController@index')->name('admin.monitor.bot.index');
                Route::post('add', 'MonitorBotController@add')->name('admin.monitor.bot.add');
                Route::post('update', 'MonitorBotController@update')->name('admin.monitor.bot.update');
                Route::post('delete', 'MonitorBotController@delete')->name('admin.monitor.bot.delete');
                Route::post('change_status', 'MonitorBotController@change_status')->name('admin.monitor.bot.change_status');
            });
            
            // 监控钱包
            Route::group(['prefix' => 'wallet'], function () {
                Route::get('get_data', 'MonitorWalletController@getData')->name('admin.monitor.wallet.get_data');
                Route::get('index', 'MonitorWalletController@index')->name('admin.monitor.wallet.index');
                Route::post('add', 'MonitorWalletController@add')->name('admin.monitor.wallet.add');
                Route::post('batchadd', 'MonitorWalletController@batchadd')->name('admin.monitor.wallet.batchadd');
                Route::post('update', 'MonitorWalletController@update')->name('admin.monitor.wallet.update');
                Route::post('delete', 'MonitorWalletController@delete')->name('admin.monitor.wallet.delete');
                Route::post('change_status', 'MonitorWalletController@change_status')->name('admin.monitor.wallet.change_status');
            });
        });
        
        // 归集管理
        Route::group(['namespace' => 'Collection', 'prefix' => 'collection', 'middleware'=>'XSS'], function () {
            // 归集钱包
            Route::group(['prefix' => 'wallet'], function () {
                Route::get('get_data', 'CollectionWalletController@getData')->name('admin.collection.wallet.get_data');
                Route::get('index', 'CollectionWalletController@index')->name('admin.collection.wallet.index');
                Route::post('add', 'CollectionWalletController@add')->name('admin.collection.wallet.add');
                Route::post('update', 'CollectionWalletController@update')->name('admin.collection.wallet.update');
                Route::post('delete', 'CollectionWalletController@delete')->name('admin.collection.wallet.delete');
                Route::post('change_status', 'CollectionWalletController@change_status')->name('admin.collection.wallet.change_status');
                Route::post('updateprikey', 'CollectionWalletController@updateprikey')->name('admin.collection.wallet.updateprikey');
            });
            // 归集记录
            Route::group(['prefix' => 'list'], function () {
                Route::get('get_data', 'CollectionListController@getData')->name('admin.collection.list.get_data');
                Route::get('index', 'CollectionListController@index')->name('admin.collection.list.index');
            });
        });
        
        // 商城管理
        Route::group(['namespace' => 'Shop', 'prefix' => 'shop', 'middleware'=>'XSS'], function () {
            // 商品管理
            Route::group(['prefix' => 'goods'], function () {
                Route::get('get_data', 'ShopGoodsController@getData')->name('admin.shop.goods.get_data');
                Route::get('index', 'ShopGoodsController@index')->name('admin.shop.goods.index');
                Route::post('add', 'ShopGoodsController@add')->name('admin.shop.goods.add');
                Route::get('show', 'ShopGoodsController@show')->name('admin.shop.goods.show');
                Route::post('update', 'ShopGoodsController@update')->name('admin.shop.goods.update');
                Route::post('delete', 'ShopGoodsController@delete')->name('admin.shop.goods.delete');
                Route::post('change_status', 'ShopGoodsController@change_status')->name('admin.shop.goods.change_status');
            });
            
            // 卡密管理
            Route::group(['prefix' => 'cdkey'], function () {
                Route::get('get_data', 'ShopGoodsCdkeyController@getData')->name('admin.shop.cdkey.get_data');
                Route::get('index', 'ShopGoodsCdkeyController@index')->name('admin.shop.cdkey.index');
                Route::post('add', 'ShopGoodsCdkeyController@add')->name('admin.shop.cdkey.add');
                Route::post('batchadd', 'ShopGoodsCdkeyController@batchadd')->name('admin.shop.cdkey.batchadd');
                Route::post('show', 'ShopGoodsCdkeyController@show')->name('admin.shop.cdkey.show');
                Route::post('update', 'ShopGoodsCdkeyController@update')->name('admin.shop.cdkey.update');
                Route::post('delete', 'ShopGoodsCdkeyController@delete')->name('admin.shop.cdkey.delete');
                Route::post('batchshang', 'ShopGoodsCdkeyController@batchshang')->name('admin.shop.cdkey.batchshang');
                Route::post('batchxia', 'ShopGoodsCdkeyController@batchxia')->name('admin.shop.cdkey.batchxia');
            });
            
            // 机器人商品
            Route::group(['prefix' => 'bot'], function () {
                Route::get('get_data', 'ShopGoodsBotController@getData')->name('admin.shop.bot.get_data');
                Route::get('index', 'ShopGoodsBotController@index')->name('admin.shop.bot.index');
                Route::post('add', 'ShopGoodsBotController@add')->name('admin.shop.bot.add');
                Route::post('update', 'ShopGoodsBotController@update')->name('admin.shop.bot.update');
                Route::post('delete', 'ShopGoodsBotController@delete')->name('admin.shop.bot.delete');
                Route::post('change_status', 'ShopGoodsBotController@change_status')->name('admin.shop.bot.change_status');
            });
            
            // 商品订单
            Route::group(['prefix' => 'order'], function () {
                Route::get('get_data', 'ShopOrderController@getData')->name('admin.shop.order.get_data');
                Route::get('index', 'ShopOrderController@index')->name('admin.shop.order.index');
                Route::post('update', 'ShopOrderController@update')->name('admin.shop.order.update');
            });
        });
        
        // 系统设置
        Route::group(['namespace' => 'Setting', 'prefix' => 'setting', 'middleware'=>'XSS'], function () {
            // 配置信息
            Route::group(['prefix' => 'config'], function () {
                Route::get('index', 'ConfigController@index')->name('admin.setting.config.index');
                Route::post('update', 'ConfigController@update')->name('admin.setting.config.update');
                /* ueditor上传图片 */
                Route::post('uploadfile', 'ConfigController@uploadfile')->name('admin.setting.config.uploadfile');
                Route::post('uploadfileNew', 'ConfigController@uploadfileNew')->name('admin.setting.config.uploadfileNew');
                /* 清理job缓存 */
                Route::post('clearjobcache', 'ConfigController@clearjobcache')->name('admin.setting.config.clearjobcache');
                /* 检测job状态 */
                Route::get('checkjob', 'ConfigController@checkjob')->name('admin.setting.config.checkjob');
            });
            //数据字典
            Route::group(['prefix' => 'dictionary'], function () { 
                Route::any('index', 'DictionaryController@index')->name('admin.setting.dictionary.index');
                Route::post('store', 'DictionaryController@store')->name('admin.setting.dictionary.store');
                Route::post('update', 'DictionaryController@update')->name('admin.setting.dictionary.update');
                Route::post('delete', 'DictionaryController@delete')->name('admin.setting.dictionary.delete');
            });
        });

        // 系统管理
        Route::group(['namespace' => 'System', 'prefix' => 'system', 'middleware'=>'XSS'], function () {
            // 管理员管理
            Route::group(['prefix' => 'admin'], function () {
                Route::get('index', 'AdminController@index')->name('admin.system.admin.index');
                Route::get('get_data', 'AdminController@getData')->name('admin.system.admin.get_data');
                Route::post('change_status', 'AdminController@changeStatus')->name('admin.system.admin.change_status');
                Route::post('add', 'AdminController@add')->name('admin.system.admin.add');
                Route::post('delete', 'AdminController@delete')->name('admin.system.admin.delete');
                Route::post('update', 'AdminController@update')->name('admin.system.admin.update');
                Route::post('change_password', 'AdminController@changePassword')->name('admin.system.admin.change_password');
            });

            // 权限管理
            Route::group(['prefix' => 'permission'], function () {
                Route::get('index', 'PermissionController@index')->name('admin.system.permission.index');
                Route::post('get_data', 'PermissionController@getData')->name('admin.system.permission.get_data');
                Route::post('add', 'PermissionController@add')->name('admin.system.permission.add');
                Route::post('update', 'PermissionController@update')->name('admin.system.permission.update');
                Route::get('del', 'PermissionController@del')->name('admin.system.permission.del');
                Route::get('get_item', 'PermissionController@getItem')->name('admin.system.permission.get_item');
            });
            // 角色管理
            Route::group(['prefix' => 'role'], function () {
                Route::get('index', 'RoleController@index')->name('admin.system.role.index');
                Route::get('get_data', 'RoleController@getData')->name('admin.system.role.get_data');
                Route::post('add', 'RoleController@add')->name('admin.system.role.add');
                Route::post('update', 'RoleController@update')->name('admin.system.role.update');
                Route::post('del', 'RoleController@del')->name('admin.system.role.del');
                Route::post('permission_data', 'RoleController@permissionData')->name('admin.system.role.permission_data');
                Route::get('show_permissions/{id}', 'RoleController@showPermissions')->name('admin.system.role.show_permissions');
                Route::post('change_permission', 'RoleController@changePermission')->name('admin.system.role.change_permission');

            }); 
        });
    });

});
