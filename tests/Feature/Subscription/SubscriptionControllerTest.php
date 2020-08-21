<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Laravel\Paddle\Billable;
use Laravel\Paddle\Concerns\ManagesSubscriptions;
use Laravel\Paddle\Subscription;
use Laravel\Paddle\SubscriptionBuilder;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->actingAs($this->user);
    }

    /**
     * @test
     */
    public function create_renders_with_0_trial_days_if_subscription_exists()
    {
        $paylink = 'http://foo.bar';

        $subscriptionMock = $this->createMock(Subscription::class);

        $subscriptionBuilderMock = $this->createMock(SubscriptionBuilder::class);
        $subscriptionBuilderMock->method('trialDays')->willReturnSelf();
        $subscriptionBuilderMock->method('create')->willReturn($paylink);

        $userMock = $this->createMock(User::class);
        $userMock->method('subscription')->willReturn($subscriptionMock);
        $userMock->method('subscribed')->willReturn(true);
        $userMock->method('newSubscription')->willReturn($subscriptionBuilderMock);

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($userMock);

        $subscriptionBuilderMock->expects($this->once())->method('trialDays')->with(0);
        $subscriptionBuilderMock->expects($this->once())->method('create');

        $response = $this->get(route('subscription.create'));
        $response->assertOk();
    }

    /**
     * @test
     */
    public function await_renders_if_no_receipt_yet()
    {
        $checkoutId = '64294199-chref4b2b852724-c2d7392sad6';

        $response = $this->get(route('subscription.await', ['checkout' => $checkoutId]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function await_redirects_if_receipt_is_found()
    {
        $user = factory(User::class)->create();
        $paddleId = 9999999;
        $checkoutId = '64294199-chref4b2b852724-c2d7392sad6';

        DB::table('receipts')->insert(
            [
                'billable_id' => $user->getAttribute('id'),
                'billable_type' => User::class,
                'paddle_subscription_id' => $paddleId,
                'checkout_id' => $checkoutId,
                'order_id' => '17024121-1320200086',
                'amount' => 0,
                'tax' => 0,
                'currency' => 'USD',
                'quantity' => 1,
                'receipt_url' => "http://my.paddle.com/receipt/17024121-13001986/{$checkoutId}",
                'paid_at' => '2020-08-19 09:45:09',
                'created_at' => '2020-08-19 09:45:08',
                'updated_at' => '2020-08-19 09:45:08'
            ]
        );

        DB::table('subscriptions')->insert(
            [
                'billable_id' => $user->getAttribute('id'),
                'billable_type' => User::class,
                'name' => 'hobby',
                'paddle_id' => $paddleId,
                'paddle_status' => 'trailing',
                'paddle_plan' => 999999,
                'quantity' => 1,
                'trial_ends_at' => '2020-09-02 00:00:00',
                'created_at' => '2020-08-19 09:45:08',
                'updated_at' => '2020-08-19 09:45:08'
            ]
        );

        $response = $this->get(route('subscription.await', ['checkout' => $checkoutId]));
        $response->assertRedirect(route('project.create'));
    }

    /**
     * @test
     */
    public function expired_returns_false_if_not_subscribed()
    {
        $billable = $this->createMock(User::class);
        $billable->expects($this->any())->method('subscribed')->willReturn(false);

        Auth::shouldReceive('user')->andReturn($billable);

        $response = $this->get(route('subscription.check'));

        $response->assertJson(['subscribed' => false]);
    }


    /**
     * @test
     */
    public function expired_returns_true_if_subscribed()
    {
        $billable = $this->createMock(User::class);
        $billable->expects($this->any())->method('subscribed')->willReturn(true);

        Auth::shouldReceive('user')->andReturn($billable);

        $response = $this->get(route('subscription.check'));

        $response->assertJson(['subscribed' => true]);
    }


    /**
     * @test
     */
    public function missing()
    {
        $response = $this->get(route('subscription.missing'));

        $response->assertOk();
    }
}
