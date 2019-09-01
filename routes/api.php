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
    Route::get('raffle/top', 'RaffleController@top');  // 置顶的抽奖
    Route::get('raffle/{raffle}', 'RaffleController@show')->where('raffle', '[0-9]+'); // 抽奖详情
    Route::post('raffle', 'RaffleController@store');    // 发起抽奖
    Route::post('raffle/upload_award', 'RaffleController@uploadAwardPic'); // 上传奖品图
    Route::post('raffle/upload_context', 'RaffleController@uploadContext'); // 上传奖品图
    Route::post('raffle/upload_subs', 'RaffleController@uploadSubscription'); // 上传奖品图

    Route::get('raffle/launch', 'RaffleController@launchedRaffle'); // 我发起的抽奖
    Route::get('raffle/participate', 'UserRaffleController@participatedRaffle');  // 我参与的抽奖
    Route::post('raffle/participate/{raffle}', 'UserRaffleController@store');   // 参与抽奖


    // 用户联系方式
    Route::get('users/contacts', 'UserContactsController@index');
    Route::post('users/contacts', 'UserContactsController@store');
    Route::put('users/contacts/{contact}', 'UserContactsController@update');
    Route::delete('users/contacts/{contact}', 'UserContactsController@destroy');

    Route::post('store_form', 'AuthController@storeFormId'); // 记录用户的form_id
});

// 当前抽奖列表

// 编辑抽奖信息

// 用户信息授权

// 我的全部抽奖列表

// 我发起的抽奖

// 我的中奖记录

// 参与抽奖


