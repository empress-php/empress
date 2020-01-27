<?php

namespace Empress\Test;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Router;
use League\Uri\Http;

trait HelperTrait
{
    public function createMockRequest(string $method, string $uri, array $params = [])
    {
        $client = $this->createMock(Client::class);
        $request = new Request($client, $method, Http::createFromString($uri));
        $request->setAttribute(Router::class, $params);

        return $request;
    }
}