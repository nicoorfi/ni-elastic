<?php

declare(strict_types=1);

namespace Sigmie\Base\APIs;

use GuzzleHttp\Psr7\Uri;
use Sigmie\Base\Contracts\API;
use Sigmie\Base\Contracts\ElasticsearchResponse;
use Sigmie\Base\Http\ElasticsearchRequest;

trait Stats
{
    use API;

    public function statsAPICall(string $index): ElasticsearchResponse
    {
        $uri = new Uri("{$index}/_stats");

        $esRequest = new ElasticsearchRequest('GET', $uri);

        return $this->httpCall($esRequest);
    }
}