<?php

use Illuminate\Support\Facades\Route;

Route::get('raffle', 'RaffleController@index');
Route::get('raffle/{raffle}', 'RaffleController@show');

Route::post('raffle/whitelist', 'RaffleWhitelistController@store');
