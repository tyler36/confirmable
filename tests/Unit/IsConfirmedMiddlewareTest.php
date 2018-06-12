<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tyler36\ConfirmableTrait\Middleware\isConfirmed;

/**
 * Class IsConfirmedMiddlewareTest
 *
 * @test
 * @group middleware
 * @group auth
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class IsConfirmedMiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var string
     */
    protected $route;

    /**
     * SETUP:           Add a fake route for testing
     */
    public function setUp()
    {
        parent::setUp();

        // SETUP:       Testing routes
        $this->route = 'test/is-confirmed';

        app('router')->get($this->route, function () {
            return 'passed!';
        })->middleware(isConfirmed::class);
    }

    /**
     * @test
     * @group guest
     */
    public function guests_are_redirected_to_login()
    {
        $this->assertTrue(auth()->guest());

        // VISIT:       Page and assert redirect
        $this->withExceptionHandling()
            ->get($this->route)
            ->assertRedirect(route('login'));
    }

    /**
     * @test
     * @group member
     */
    public function unconfirmed_members_are_redirected_to_a_confirmation_page()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();

        // VISIT:       Page as unverified user and assert redirect
        $this->actingAs($user)
            ->get($this->route)
            ->assertRedirect(route('confirm.edit'));
    }

    /**
     * @test
     * @group member
     * @group exception
     * @group 403
     */
    public function unconfirmed_members_via_ajax_are_sent_error_code_with_warning()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();

        // VISIT:       Page as unverified user and assert redirect
        $response = $this->actingAs($user)
            ->get($this->route, ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(403);
    }

    /**
     * @test
     * @group member
     */
    public function confirmed_members_can_access_page()
    {
        $user = factory(User::class)->states('isConfirmed')->create();

        // VISIT:       Page and assert content
        $this->actingAs($user)
            ->get($this->route)
            ->assertStatus(200)
            ->assertSee('passed!');
    }
}
