<?php

use Illuminate\Support\Facades\Route;

Route::post('login', 'AuthController@login');

Route::group(['middleware' => 'auth:admin'], function () {
    Route::post('logout', 'AuthController@logout');

    Route::get('raffle', 'RaffleController@index');  // 抽奖列表
    Route::get('raffle/{raffle}', 'RaffleController@show');  // 抽奖详情

    Route::post('raffle/whitelist', 'RaffleWhitelistController@store'); // 配置白名单
});



