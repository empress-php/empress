<?php

namespace Empress\Test;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Options;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Session;
use League\Uri\Http;
use Psr\Log\LoggerInterface;
use function Amp\Socket\listen;

trait HelperTrait
{
    private function createMockRequest(string $method, string $uri, array $params = [])
    {
        $client = $this->getMockBuilder(Client::class)->getMock();
        $client->method('getLocalPort')->willReturn(1234);
        $client->method('getLocalAddress')->willReturn('example.com');

        $request = new Request($client, $method, Http::createFromString($uri));
        $request->setAttribute(Router::class, $params);

        $session = new Session(new InMemoryStorage(), 0);
        $request->setAttribute(Session::class, $session);

        return $request;
    }

    private function createMockServer()
    {
        $options = new Options;
        $socket = listen('127.0.0.1:0');

        return new Server(
            [$socket],
            $this->createMock(RequestHandler::class),
            $this->createMock(LoggerInterface::class),
            $options
        );
    }

}
