<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Raffle;
use Faker\Generator as Faker;

$factory->define(Raffle::class, function (Faker $faker) {
    $randomDay = random_int(0, 7);
    $userIds = \App\Models\User::query()->whereNotNull('nick_name')->select(['id'])->get()->pluck('id')->all();
    return [
        'draw_type' => Raffle::DRAW_BASE_ON_TIME,
        'draw_time' => \Carbon\Carbon::now()->addDays($randomDay),
        'award_type' => 1,
        'desc' => $faker->sentence,
        'context' => $faker->text,
        'user_id' => $faker->randomElement($userIds),
    ];
});
