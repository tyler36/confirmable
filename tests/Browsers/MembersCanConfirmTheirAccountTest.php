<?php

namespace Tests\Browser;

use App\Confirmation;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Class MembersCanConfirmTheirAccountTest
 *
 *
 * @test
 * @group   confirm
 * @group   member
 * @group   browser
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class MembersCanConfirmTheirAccountTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * @group mail
     */
    public function members_can_request_a_new_token_and_verify()
    {
        $this->browse(function (Browser $browser) {
            $user = factory(User::class)->states('isNotConfirmed')->create();
            $confirmation = $user->confirmation;

            $submit = ucwords(trans('form.submit'));

            // VISIT:       Page as User
            $browser->loginAs($user)
                ->visit(route('confirm.edit'))
                ->type('email', $user->email)
                ->type('token', 'abc')
                ->check('terms')
                ->press($submit)
                ->waitForText(trans('error.general'));

            // ASSERT:  Resend token
            $browser->click('a.resend')
                ->waitForText(trans('confirmable::message.resent'));

            // ASSERT:  New token was generated
            $newToken = Confirmation::where('email', $user->email)->firstOrFail();
            $this->assertNotSame($confirmation->token, $newToken->token);

            $browser->type('email', $user->email)
                ->type('token', $newToken->token)
                ->check('terms')
                ->press($submit)
                ->assertSee(trans('confirmable::message.confirm.success'));
        });
    }
}
