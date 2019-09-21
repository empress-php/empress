<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\PHPUnit\AsyncTestCase;
use Psr\Log\LoggerInterface;
use function Amp\Socket\listen;

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
