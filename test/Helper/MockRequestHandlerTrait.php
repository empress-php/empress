<?php

namespace Empress\Test\Helper;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Success;

trait MockRequestHandlerTrait
{
    private function createDefaultMockRequestHandler(Request $request): RequestHandler
    {
        $mockRequestHandler = $this->createMock(RequestHandler::class);
        $mockRequestHandler
            ->expects(static::once())
            ->method('handleRequest')
            ->with(static::identicalTo($request))
            ->willReturn(new Success());

        return $mockRequestHandler;
    }
}
