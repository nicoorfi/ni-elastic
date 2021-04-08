<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\Proxy\ProxyRequest;
use App\Models\Cluster;
use App\Models\ExternalCluster;
use App\Models\FileType;
use App\Models\Token;
use App\Models\User;
use App\Services\RedisStore as ServicesRedisStore;
use Illuminate\Cache\RedisLock;
use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Sigmie\App\Core\CloudflareFactory;
use Sigmie\App\Core\Contracts\DNSFactory;
use Sigmie\App\Core\DNS\Contracts\Provider as DNSProvider;
use YlsIdeas\FeatureFlags\Facades\Features;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::ignoreMigrations();
        Sanctum::usePersonalAccessTokenModel(Token::class);

        $this->app->singleton(ProxyRequest::class);

        $this->app->singleton(DNSFactory::class, function () {
            return new CloudflareFactory(
                config('services.cloudflare.api_token'),
                config('services.cloudflare.zone_id'),
                config('services.cloudflare.domain')
            );
        });

        $this->app->singleton(DNSProvider::class, function () {
            return app(DNSFactory::class)->create();
        });

        $this->app->singleton(LockProvider::class, function () {
            return Cache::getStore();
        });

        $this->app->extend(\Illuminate\Bus\Dispatcher::class, function ($dispatcher, $app) {
            return new \App\Services\Dispatcher($app, $dispatcher);
        });

        $this->app->extend(\Illuminate\Cache\CacheManager::class, function ($manager, $app) {
            return new \App\Services\CacheManager($app);
        });
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

        Cache::macro('lockExists', function ($lock) {
            return !is_null(Cache::get($lock));
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Relation::morphMap([
            'file' => FileType::class,
            'user' => User::class,
            'cluster' => Cluster::class,
            'external_cluster' => ExternalCluster::class
        ]);

        Queue::before(function (JobProcessing $event) {
            $job = $event->job;

            $context = [
                'name' => $job->getName(),
                'queue' => $job->getQueue(),
                'maxTries' => $job->maxTries(),
            ];

            Log::info('Job Processing', $context);
        });

        Queue::after(function (JobProcessed $event) {

            $job = $event->job;

            $context = [
                'name' => $job->getName(),
                'queue' => $job->getQueue(),
                'maxTries' => $job->maxTries(),
                'attempts' => $job->attempts(),
            ];

            if ($job->hasFailed()) {
                Log::error('Job Failed', $context);
                return;
            }

            Log::info('Job Processed', $context);
        });
    }
}
