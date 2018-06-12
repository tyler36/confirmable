<?php

namespace Tests\Feature;

use App\Confirmation;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class ConfirmationTraitTest
 *
 * @test
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ConfirmationTraitTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->model = $this->getMockForTrait('Tyler36\ConfirmableTrait\Confirmable');
    }

    /**
     * @test
     * @group trait
     */
    public function a_user_know_if_they_are_confirmed()
    {
        $this->model->confirmed = false;
        $this->assertTrue($this->model->isNotConfirmed());
        $this->assertFalse($this->model->isConfirmed());

        $this->model->confirmed = true;
        $this->assertFalse($this->model->isNotConfirmed());
        $this->assertTrue($this->model->isConfirmed());
    }

    /**
     * @test
     * @group relationship
     */
    public function a_user_has_confirm_token()
    {
        $user  = factory(User::class)->create();
        $token = factory(Confirmation::class)->make(['email' => null]);

        // ASSERT:      User can save 'token'
        $saved = $user->confirmation()->save($token);
        $this->assertTrue($saved->exists);
        $this->assertInstanceOf(Confirmation::class, $user->confirmation);
    }

    /**
     * @test
     */
    public function a_user_can_generate_a_new_confirmation()
    {
        $user = factory(User::class)->create();

        $this->assertDatabaseMissing('confirmations', [
            'email' => $user->email,
        ]);

        $user->createNewConfirmationToken();

        $this->assertDatabaseHas('confirmations', [
            'email' => $user->email,
        ]);
    }

    /**
     * @test
     */
    public function a_user_can_generate_a_updated_confirmation_token()
    {
        $user = factory(User::class)->create();
        $user->createNewConfirmationToken();
        $token = $user->confirmation->token;

        $this->assertDatabaseHas('confirmations', [
            'email' => $user->email,
            'token' => $token,
        ]);

        $user->createNewConfirmationToken();
        $this->assertNotSame($token, $user->fresh()->confirmation->token);

        $this->assertDatabaseMissing('confirmations', [
            'email' => $user->email,
            'token' => $token,
        ]);
        $this->assertDatabaseHas('confirmations', [
            'email' => $user->email,
        ]);
    }
}
