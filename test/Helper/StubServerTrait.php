<?php

declare(strict_types=1);

namespace Empress\Test\Helper;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Server;
use Amp\Socket\Server as SocketServer;
use Psr\Log\LoggerInterface;

trait StubServerTrait
{
    private function getStubServer(): Server
    {
        $socket = \fopen('/dev/null', 'rb');
        $socketServer = new SocketServer($socket);

        return new Server(
            [$socketServer],
            $this->createMock(RequestHandler::class),
            $this->createMock(LoggerInterface::class)
        );
    }
}
