<?php

namespace Empress\Test\Helper;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Session;
use Empress\Routing\Router;
use League\Uri\Http;

trait MockRequestTrait
{
    private function createMockRequest(string $method = 'GET', string $uri = '/', array $params = [], $includeSession = true)
    {
        $client = $this->getMockBuilder(Client::class)->getMock();
        $client->method('getLocalPort')->willReturn(1234);
        $client->method('getLocalAddress')->willReturn('example.com');

        $request = new Request($client, $method, Http::createFromString($uri));
        $request->setAttribute(Router::class, $params);

        if ($includeSession) {
            $session = new Session(new InMemoryStorage(), 0);
            $request->setAttribute(Session::class, $session);
        }

        return $request;
    }
}
