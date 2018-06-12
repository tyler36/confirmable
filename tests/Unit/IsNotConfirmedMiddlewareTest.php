<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tyler36\ConfirmableTrait\Middleware\isNotConfirmed;

/**
 * Class IsNotConfirmedMiddlewareTest
 *
 * @test
 * @group middleware
 * @group auth
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class IsNotConfirmedMiddlewareTest extends TestCase
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

        // SETUP:       Testing route
        $this->route = 'test/middleware';

        app('router')->get($this->route, function () {
            return 'passed!';
        })->middleware(isNotConfirmed::class);
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
    public function confirmed_members_are_redirected_to_their_profile()
    {
        $user = factory(User::class)->states('isConfirmed')->create();

        // VISIT:       Page as unverified user and assert redirect
        $this->actingAs($user)
            ->get($this->route)
            ->assertRedirect(route('user.show', $user))
            ->assertSessionHas('message', trans('confirmable.exists'));
    }

    /**
     * @test
     * @group member
     */
    public function confirmed_members_via_ajax_are_sent_error_code_with_warning()
    {
        $user = factory(User::class)->states('isConfirmed')->create();

        // VISIT:       Page as unverified user and assert redirect
        $response = $this->actingAs($user)
            ->get($this->route, ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(400)
            ->getOriginalContent();

        $this->assertTrue($response['errors']);
        $this->assertContains(trans('confirmable.exists'), $response['message']);
    }

    /**
     * @test
     * @group member
     */
    public function unconfirmed_members_can_access_page()
    {
        $user = factory(User::class)->states('isNotConfirmed')->create();

        // VISIT:       Page and assert content
        $this->actingAs($user)
            ->get($this->route)
            ->assertStatus(200)
            ->assertSee('passed!');
    }
}
