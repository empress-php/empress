<?php

namespace Empress\Test\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Middleware\Filter\BeforeMiddleware;
use Empress\Middleware\Filter\FilterHandler;
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

        $beforeHandler = new FilterHandler(function (Context $ctx) use (&$flag) {
            $flag .= '2';
        });

        $middleware = new BeforeMiddleware();
        $middleware->addHandler($beforeHandler);

        $request = $this->createMockRequest();

        yield $middleware->handleRequest($request, $handler);

        $this->assertEquals('21', $flag);
    }
}
