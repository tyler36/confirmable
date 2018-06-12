<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class ConfirmableUserStatesFactoryTest
 *
 * @test
 * @group confirmable
 * @group factory
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ConfirmableUserStatesFactoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function a_confirmation_model_is_created_after_using_is_not_confirmed_state()
    {
        // ASSERT:      Database missing email
        $email = 'example@example.com';
        $this->assertDatabaseMissing('confirmations', [
            'email' => $email,
        ]);

        $user = factory(User::class)->states('isNotConfirmed')->create(['email' => $email]);

        $this->assertDatabaseHas('confirmations', [
            'email' => $email,
        ]);
    }
}
