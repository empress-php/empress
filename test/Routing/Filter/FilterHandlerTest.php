<?php

namespace Empress\Test\Routing\Filter;

use Empress\Routing\Filter\FilterHandler;
use PHPUnit\Framework\TestCase;

class FilterHandlerTest extends TestCase
{
    public function testFilterHandler()
    {
        $callable = fn () => null;
        $handler = new FilterHandler($callable, '/foo/bar/*');

        static::assertEquals($callable, $handler->getCallable());
        static::assertEquals('/foo/bar/*', $handler->getPath());
    }
}
