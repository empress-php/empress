<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Server;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\PlainTextTransformer;
use Empress\Routing\RouteConfigurator;
use Empress\Routing\RouterBuilder;
use League\Uri\Http;
use Psr\Log\LoggerInterface;
use function Amp\Socket\listen;

class RouterBuilderTest extends AsyncTestCase
{
    public function testNoRoutesRegistered()
    {
        $this->expectException(\Throwable::class);

        $configurator = new RouteConfigurator();
        $builder = new RouterBuilder($configurator);
        $router = $builder->getRouter();

        $server = $this->createServer($router);

        yield $server->start();
        yield $server->stop();
    }

    public function testHandleUnprefixedRoutes()
    {
        $configurator = new RouteConfigurator();
        $configurator->get('/hello', function () {
            return 'Hello, World!';
        }, new PlainTextTransformer());

        $builder = new RouterBuilder($configurator);
        $router = $builder->getRouter();
        $router->onStart($this->createServer($this->createMock(RequestHandler::class)));

        $request = new Request($this->createMock(Client::class), 'GET', Http::createFromString('/hello'));
        $response = yield $router->handleRequest($request);

        $this->assertEquals(Status::OK, $response->getStatus());

        $request = new Request($this->createMock(Client::class), 'GET', Http::createFromString('/world'));
        $response = yield $router->handleRequest($request);

        $this->assertEquals(Status::NOT_FOUND, $response->getStatus());
    }

    public function testHandlePrefixedRoutes()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix1', function ($c) {
            $c->prefix('/prefix2', function ($c) {
                $c->get('/hello', function () {
                    return 'Hello, World!';
                });
            });
        }, new PlainTextTransformer());

        $builder = new RouterBuilder($configurator);
        $router = $builder->getRouter();
        $router->onStart($this->createServer($this->createMock(RequestHandler::class)));

        $request = new Request($this->createMock(Client::class), 'GET', Http::createFromString('/prefix1/prefix2/hello'));
        $response = yield $router->handleRequest($request);

        $this->assertEquals(Status::OK, $response->getStatus());
    }

    private function createServer(RequestHandler $handler): Server
    {
        return new Server(
            [listen('127.0.0.1:0')],
            $handler,
            $this->createMock(LoggerInterface::class)
        );
    }
}
