<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 接口
Route::group(['prefix' => 'api', 'namespace' => 'Api', 'middleware'=>'XSS'], function(){
	Route::group(['namespace' => 'Telegram'], function() {
        // tg消息webhook
    	Route::post('telegram/getdata', 'TelegramController@getdata')->name('api.telegram.getdata');
        
        Route::post('test/getdata', 'TestController@getdata')->name('api.test.getdata');
    });
    
    Route::group(['namespace' => 'ThirdPart'], function() {
        // 查询余额
    	Route::get('thirdpart/balance', 'ThirdPartController@balance')->name('api.thirdpart.balance');
    	// 笔数下单
        Route::post('thirdpart/bishuorder', 'ThirdPartController@bishuorder')->name('api.thirdpart.bishuorder');
        // 闪租下单
        Route::post('thirdpart/shanzuorder', 'ThirdPartController@shanzuorder')->name('api.thirdpart.shanzuorder');
    });
});