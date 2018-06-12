<?php

use App\Confirmation;
use App\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Tyler36/Confirmable
|--------------------------------------------------------------------------
*/
/*
 * Create 'not confirmed' User state
 */
$factory->state(User::class, 'isNotConfirmed', function (Faker $faker) {
    return [
        'confirmed' => false,
    ];
});

$factory->afterCreatingState(User::class, 'isNotConfirmed', function ($user, $faker) {
    $user->confirmation()->save(
        factory(Confirmation::class)->make(['email' => null])
    );
});

/*
 * Create 'confirmed' User state
 */
$factory->state(User::class, 'isConfirmed', function (Faker $faker) {
    return [
        'confirmed' => true,
    ];
});
