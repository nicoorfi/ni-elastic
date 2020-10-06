<?php

namespace App\Http\Middleware\Proxy;

use App\Http\Controllers\Cluster\TokenController;
use App\Models\Cluster;
use Closure;

class VerifyTokenStatus
{
    protected Cluster $cluster;

    protected ProxyRequest $proxyRequest;

    public function __construct(ProxyRequest $proxyRequest)
    {
        $this->proxyRequest = $proxyRequest;
    }

    private function isSearchTokenRequest(): bool
    {
        return $this->cluster->currentAccessToken()->getAttribute('name') === TokenController::SEARCH_ONLY;
    }

    private function isAdminTokenRequest(): bool
    {
        return $this->cluster->currentAccessToken()->getAttribute('name') === TokenController::ADMIN;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function handle($request, Closure $next)
    {
        $this->cluster = $this->proxyRequest->cluster();

        if ($this->isAdminTokenRequest() && $this->cluster->isAdminTokenActive()) {
            return $next($request);
        }

        if ($this->isSearchTokenRequest() && $this->cluster->isSearchTokenActive()) {
            return $next($request);
        }

        return response()->json(['message' => 'Inactive token.'], 403);
    }
}