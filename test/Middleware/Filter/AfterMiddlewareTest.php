<?php

namespace Empress\Test\Middleware\Filter;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Middleware\Filter\AfterMiddleware;
use Empress\Middleware\Filter\FilterHandler;
use Empress\Test\HelperTrait;

class AfterMiddlewareTest extends AsyncTestCase
{
    use HelperTrait;

    public function testHandleRequest()
    {
        $flag = '';

        $handler = new RequestHandler(function (Context $ctx) use (&$flag) {
            $flag .= '1';
        });

        $afterHandler = new FilterHandler(function (Context $ctx) use (&$flag) {
            $flag .= '2';
        });

        $middleware = new AfterMiddleware;
        $middleware->addHandler($afterHandler);

        $request = $this->createMockRequest();

        yield $middleware->handleRequest($request, $handler);

        $this->assertEquals('12', $flag);
    }
}
