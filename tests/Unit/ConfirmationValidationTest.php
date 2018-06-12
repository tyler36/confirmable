<?php

use App\Confirmation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class ConfirmationValidationTest
 *
 * @test
 * @group unit
 * @group validation
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ConfirmationValidationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function token_is_within_valid_period()
    {
        // SETUP:       Create invalid token: Over 24 hours ago
        $confirmation = factory(Confirmation::class)->create(['updated_at' => now()->subHours(25)]);

        // VALIDATE
        $validate = $confirmation->validateToken($confirmation->token);
        $this->assertTrue($validate->fails());
        $this->assertSame($validate->errors()->first('updated_at'), trans('confirmable::message.token.expired'));
    }

    /**
     * @test
     */
    public function token_matches_provided_token()
    {
        // SETUP:       Create confirmation
        $confirmation = factory(Confirmation::class)->create();

        // VALIDATE
        $validate = $confirmation->validateToken('abc');
        $this->assertTrue($validate->fails());
        $this->assertSame($validate->errors()->first('token'), trans('confirmable::message.token.mismatch'));

        $validate = $confirmation->validateToken($confirmation->token);
        $this->assertTrue($validate->passes());
    }
}
