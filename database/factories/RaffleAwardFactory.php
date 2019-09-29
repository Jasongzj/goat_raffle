<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\RaffleAward;
use Faker\Generator as Faker;

$factory->define(RaffleAward::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'amount' => random_int(1, 5),
    ];
});
