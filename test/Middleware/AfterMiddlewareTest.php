<?php

namespace Empress\Test\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Middleware\AfterMiddleware;
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

        $afterHandler = function (Context $ctx) use (&$flag) {
            $flag .= '2';
        };

        $request = $this->createMockRequest('GET', '/');
        $middleware = new AfterMiddleware($afterHandler);

        yield $middleware->handleRequest($request, $handler);

        $this->assertEquals('12', $flag);
    }
}
