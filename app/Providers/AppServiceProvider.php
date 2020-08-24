<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\ProxyRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Sanctum\Sanctum;
use YlsIdeas\FeatureFlags\Facades\Features;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Inertia::share('flash', function () {
            return [
                'success' => Session::get('success'),
                'error' => Session::get('error'),
            ];
        });

        Inertia::share('old', function () {
            return Session::getOldInput();
        });

        Inertia::share('csrf_token', function () {
            return csrf_token();
        });

        Inertia::share('errors', function () {

            return Session::get('errors')
                ? Session::get('errors')->getBag('default')->getMessages()
                : null;
        });

        $this->app->singleton(ProxyRequest::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Features::noBlade();
        Features::noScheduling();
        Features::noValidations();
        Features::noCommands();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
