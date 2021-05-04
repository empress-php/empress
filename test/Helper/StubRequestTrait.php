<?php

namespace Empress\Test\Helper;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Session;
use Empress\Routing\Router;
use League\Uri\Http;

trait StubRequestTrait
{
    private function createStubRequest(
        string $method = 'GET',
        string $uri = '/',
        array $params = [],
        array $wildcards = [],
        $includeSession = true
    ): Request {
        $client = $this->createMock(Client::class);

        $uri = Http::createFromString($uri)
            ->withPort(1234)
            ->withHost('example.com');

        $request = new Request($client, $method, $uri);
        $request->setAttribute(Router::NAMED_PARAMS_ATTR_NAME, $params);
        $request->setAttribute(Router::WILDCARDS_ATTR_NAME, $wildcards);

        if ($includeSession) {
            $session = new Session(new InMemoryStorage(), null);
            $request->setAttribute(Session::class, $session);
        }

        return $request;
    }
}
