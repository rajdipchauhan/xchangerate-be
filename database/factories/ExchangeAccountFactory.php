<?php

$factory->define(\App\Models\ExchangeAccount::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'exchange_id' => 'bittrex',
        'user_id' => $faker->randomNumber(1),
        'key' => str_random(10),
        'secret' => str_random(32)
    ];
});
