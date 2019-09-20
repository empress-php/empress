<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Routing\RouterBuilder;
use League\Uri\Http;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function Amp\Socket\listen;
use function Empress\Routing\controller;
use function Empress\Routing\route;

class RouterBuilderTest extends AsyncTestCase
{
    public function testEmptyStringHandler()
    {
        $this->expectException(\TypeError::class);

        $container = $this->createMock(ContainerInterface::class);
        $routerBuilder = new RouterBuilder($container);

        $routerBuilder->routes(controller('', route('GET', '/', '')));

        $router = $routerBuilder->getRouter();
        $server = $this->createServer($router);

        yield $server->start();
        yield $server->stop();
    }

    public function testArrayHandler()
    {
        $controller = new class {
            public function index()
            {
                return new Response(Status::OK, [], 'Hello, World!');
            }
        };

        $container = $this->createMock(ContainerInterface::class);
        $routerBuilder = new RouterBuilder($container);

        $routerBuilder->routes(controller('', route('GET', '/', [$controller, 'index'])));

        $router = $routerBuilder->getRouter();
        $server = $this->createServer($router);

        yield $server->start();

        $client = $this->createMock(Client::class);
        $request = new Request($client, 'GET', Http::createFromString('/'));

        /** @var \Amp\Http\Server\Response $response */
        $response = yield $router->handleRequest($request);

        $this->assertEquals('Hello, World!', yield $response->getBody()->read());

        yield $server->stop();
    }

    public function testClosureHandler()
    {
        $this->markTestSkipped();
    }

    public function testStringHandlerWithoutController()
    {
        $this->markTestSkipped();
    }

    public function testServingStaticContent()
    {
        $this->markTestSkipped();
    }

    public function testAlwaysFreshInstance()
    {
        $this->markTestSkipped();
    }

    private function createServer(Router $router): Server
    {
        return new Server(
            [listen('127.0.0.1:0')],
            $router,
            $this->createMock(LoggerInterface::class)
        );
    }
}
