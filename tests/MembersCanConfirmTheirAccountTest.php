<?php

namespace Tests;

use App\Confirmation;
use App\Notifications\ConfirmEmailAccount;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tyler36\ConfirmableTrait\Events\UserRequestedConfirmationEmail;

/**
 * Class MembersCanConfirmTheirAccountTest
 *
 * @test
 * @group user
 * @group member
 * @group confirmable
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class MembersCanConfirmTheirAccountTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * @group guest
     * @group error
     * @group middleware
     * @group auth
     */
    public function guests_can_not_access_confirmation_page()
    {
        // SETUP:     Assert guest
        $this->assertTrue(auth()->guest());
        $this->withExceptionHandling()
            ->get(route('confirm.edit'))
            ->assertRedirect(route('login'));

        // ASSERT:      Can not post to route
        $this->withExceptionHandling()
            ->post(route('confirm.edit'))
            ->assertRedirect(route('login'));

        // ASSERT:      Can not request new email
        $this->withExceptionHandling()
            ->get(route('confirm.resend'))
            ->assertRedirect(route('login'));
    }

    /**
     * @test
     * @group middleware
     * @group redirect
     */
    public function confirmed_members_can_not_access_confirmation_routes()
    {
        $user           = factory(User::class)->states('isConfirmed')->create();
        $message        = trans('confirmable.exists');

        // ASSERT:      Can not access page
        $this->withExceptionHandling()
            ->actingAs($user)
            ->get(route('confirm.edit'))
            ->assertSessionHas('message', $message)
            ->assertRedirect(route('user.show', $user));

        // ASSERT:      Can not post to route
        $this->withExceptionHandling()
            ->actingAs($user)
            ->post(route('confirm.edit'))
            ->assertSessionHas('message', $message)
            ->assertRedirect(route('user.show', $user));

        // ASSERT:      Can not request new email
        $this->withExceptionHandling()
            ->actingAs($user)
            ->get(route('confirm.resend'))
            ->assertSessionHas('message', $message)
            ->assertRedirect(route('user.show', $user));
    }

    /**
     * @test
     * @group validation
     * @group request
     * @group error
     */
    public function it_shows_errors_when_the_form_is_invalid()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();
        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => false,
        ]);

        // VISIT:       Send post request
        $this->withExceptionHandling()
            ->actingAs($user)
            ->post(route('confirm.update'))
            ->assertSessionHasErrors([
                'email' => trans('validation.required', ['attribute' => trans('user.email')]),
                'token' => trans('validation.required', ['attribute' => trans('confirmable::message.token')]),
                'terms' => trans('validation.accepted', ['attribute' => trans('confirmable::message.terms')]),
            ]);

        // ASSERT:      User is not verified
        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => false,
        ]);
    }

    /**
     * @test
     * @group validation
     * @group error
     */
    public function it_shows_errors_when_the_token_is_invalid()
    {
        $confirmation = factory(Confirmation::class)->create(['updated_at' => Carbon::now()->subHours(60)]);
        $user         = $confirmation->user;

        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => false,
        ]);

        // VISIT:       Send post request
        $this->withExceptionHandling()
            ->actingAs($user)
            ->post(route('confirm.update'), [
                'email' => $user->email,
                'terms' => true,
                'token' => 'invalid',
            ])
            ->assertSessionHasErrors([
                'token'      => trans('confirmable::message.token.mismatch'),
                'updated_at' => trans('confirmable::message.token.expired'),
            ]);

        // ASSERT:      User is not verified
        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => false,
        ]);
    }

    /**
     * @test
     * @group member
     * @group success
     */
    public function a_member_can_confirm_their_account()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();

        // ASSERT:      Initial database estate
        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => false,
        ])->assertDatabaseHas('confirmations', [
            'email'     => $user->email,
        ]);

        // VISIT:       Patch endpoint
        $this->withExceptionHandling()
            ->actingAs($user)
            ->post(route('confirm.update'), [
                'email' => $user->email,
                'token' => $user->confirmation->token,
                'terms' => true,
            ])
            ->assertSessionHas('success', trans('confirmable::message.confirm.success'));

        // ASSERT:      Database has been updated
        $this->assertDatabaseHas('users', [
            'name'      => $user->name,
            'confirmed' => true,
        ]);
    }

    /**
     * @test
     * @group mail
     */
    public function a_user_can_receive_mail_with_valid_confirmation_token()
    {
        Notification::fake();

        // SETUP:       Create User
        $user = factory(User::class)->create();
        event(new UserRequestedConfirmationEmail($user));

        Notification::assertSentTo($user, ConfirmEmailAccount::class, function ($notification) use (&$link) {
            $link = route('confirm.edit', ['token' => $notification->confirmation->token]);

            return $notification->url === $link;
        });
    }

    /**
     * @test
     * @group mail
     */
    public function a_member_can_request_a_new_confirmation_email()
    {
        Notification::fake();

        // SETUP:       Create User
        $user        = factory(User::class)->states('isNotConfirmed')->create();

        // ASSERT:      Database has token
        $this->assertDatabaseHas('confirmations', [
            'email' => $user->email,
            'token' => $user->confirmation->token,
        ]);

        $this->withoutExceptionHandling()
            ->actingAs($user)
            ->get(route('confirm.resend'));

        // ASSERT:      Database is missing old token
        $this->assertDatabaseMissing('confirmations', [
            'email' => $user->email,
            'token' => $user->confirmation->token,
        ]);

        // ASSERT:      ... but has an entry for user email still (assumes new token)
        $this->assertDatabaseHas('confirmations', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ConfirmEmailAccount::class, function ($notification) use (&$link) {
            $link = route('confirm.edit', ['token' => $notification->confirmation->token]);

            return $notification->url === $link;
        });
    }

    /**
     * @test
     * @group view
     */
    public function an_unconfirmed_member_can_see_a_link_on_their_profile_page()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();

        $this->get(route('user.show', $user))
            ->assertSeeText(trans('confirmable::message.required'))
            ->assertSee('href="'.route('confirm.edit').'"');
    }
}
