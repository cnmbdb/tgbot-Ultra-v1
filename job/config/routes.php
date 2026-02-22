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
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

//API接口
Router::addGroup('/api',function (){
	// 清除定时任务缓存
    Router::post('/config/clear_timing', [\App\Controller\Api\ConfigController::class, 'clearTiming']);
    
    // 系统是否启动
    Router::post('/config/check_status', [\App\Controller\Api\ConfigController::class, 'checkStatus']);
    
    // trongas笔数回调通知
    Router::post('/trongas/notice', [\App\Controller\Api\TrongasIoController::class, 'notice']);
    
    // 搜狐笔数回调通知
    Router::get('/sohu/notice', [\App\Controller\Api\SoHuController::class, 'notice']);
});