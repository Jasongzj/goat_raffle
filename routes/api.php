<?php

use Illuminate\Support\Facades\Route;

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

Route::get('wx_auth', 'AuthController@wxAuth');   // 微信授权登录


Route::group(['middleware' => 'auth:api'], function () {
    Route::get('raffle', 'RaffleController@index');  // 普通抽奖列表
    Route::get('raffle/top', 'RaffleController@top');
    Route::post('raffle', 'RaffleController@store');    // 发起抽奖
    Route::post('raffle/upload_award', 'RaffleController@uploadAwardPic'); // 上传奖品图


    // 用户联系方式
    Route::get('users/contacts', 'UserContactsController@index');
    Route::post('users/contacts', 'UserContactsController@store');
    Route::put('users/contacts/{contact}', 'UserContactsController@update');
    Route::delete('users/contacts/{contact}', 'UserContactsController@destroy');
});
// 当前抽奖列表



// 编辑抽奖信息

// 更新用户资料

// 我的抽奖列表

// 参与抽奖


