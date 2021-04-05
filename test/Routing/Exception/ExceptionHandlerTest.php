<?php

namespace Empress\Test\Routing\Exception;

use Empress\Routing\Exception\ExceptionHandler;
use Exception;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function testExceptionHandler()
    {
        $callable = fn () => null;
        $handler = new ExceptionHandler($callable, Exception::class);

        static::assertEquals(Exception::class, $handler->getExceptionClass());
        static::assertEquals($callable, $handler->getCallable());
    }
}
