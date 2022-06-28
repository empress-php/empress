<?php

declare(strict_types=1);

namespace Empress\Test;

use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\DefaultErrorHandler;

final class DefaultErrorHandlerTest extends AsyncTestCase
{
    public function testHandleError(): \Generator
    {
        $errorHandler = new DefaultErrorHandler();

        /** @var Response $response */
        $response = yield $errorHandler->handleError(500);

        self::assertSame(500, $response->getStatus());
    }
}