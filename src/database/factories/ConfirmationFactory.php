<?php

use App\Confirmation;
use App\User;
use Faker\Generator as Faker;

$factory->define(Confirmation::class, function (Faker $faker) {
    return [
        'email'   => function () {
            return factory(User::class)->create()->email;
        },
        'token'  => $faker->md5(),
    ];
});
