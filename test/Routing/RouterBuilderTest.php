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
    private function createServer(Router $router): Server
    {
        return new Server(
            [listen('127.0.0.1:0')],
            $router,
            $this->createMock(LoggerInterface::class)
        );
    }
}
