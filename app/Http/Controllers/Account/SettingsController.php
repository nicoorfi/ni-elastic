<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(string $section = 'account')
    {
        $user = Auth::user();

        $data = [
            'subscription' => $this->subscriptionData($user),
            'account' => $user->only(['username', 'email', 'avatar_url', 'created_at', 'id'])
        ];

        return Inertia::render('account/settings', ['section' => $section, 'data' => $data]);
    }

    private function subscriptionData($user)
    {
        $data = [];
        $planName = config('services.paddle.plan_name');

        $data['subscription'] = ['was_subscribed' => false];

        $subscription = $user->subscription($planName);

        if ($subscription !== null) {
            $data['was_subscribed'] = true;
            $info = $subscription->paddleInfo();
            $method = $info['payment_information']['payment_method'];
            $nextPayment = $subscription->nextPayment();
            $lastPayment = $subscription->lastPayment();

            if ($method !== 'paypal') {
                $data['card_brand'] = $subscription->cardBrand();
                $data['card_last_four'] = $subscription->cardLastFour();
                $data['card_expire_date'] = $subscription->cardExpirationDate();
            }

            $data['canceled'] = $subscription->cancelled();
            $data['ends_at'] = $subscription->getAttribute('ends_at');
            $data['plan'] = ucfirst($planName);
            $data['payment_method'] = $method;
            $data['email'] = $subscription->paddleEmail();
            $data['on_trial'] = $subscription->onTrial();
            $data['trail_ends_at'] = $subscription->getAttribute('trial_ends_at');

            if ($lastPayment !== null) {
                $data['last_payment'] = $lastPayment->date();
            }

            if ($nextPayment !== null) {
                $data['next_payment'] = $nextPayment->date();
            }
        }

        return $data;
    }
}