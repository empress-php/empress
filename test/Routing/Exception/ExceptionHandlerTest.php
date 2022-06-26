<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Exception;

use Empress\Routing\Exception\ExceptionHandler;
use Exception;
use PHPUnit\Framework\TestCase;

final class ExceptionHandlerTest extends TestCase
{
    public function testExceptionHandler(): void
    {
        $closure = fn () => null;
        $handler = new ExceptionHandler($closure, Exception::class);

        self::assertSame(Exception::class, $handler->getExceptionClass());
        self::assertSame($closure, $handler->getCallable());
    }
}
