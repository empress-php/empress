<?php

namespace Empress\Test\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Middleware\BeforeMiddleware;
use Empress\Test\HelperTrait;

class BeforeMiddlewareTest extends AsyncTestCase
{
    use HelperTrait;

    public function testHandleRequest()
    {
        $flag = '';

        $handler = new RequestHandler(function (Context $ctx) use (&$flag) {
            $flag .= '1';
        });

        $beforeHandler = function (Context $ctx) use (&$flag) {
            $flag .= '2';
        };

        $request = $this->createMockRequest('GET', '/');
        $middleware = new BeforeMiddleware($beforeHandler);

        yield $middleware->handleRequest($request, $handler);

        $this->assertEquals('21', $flag);
    }
}
