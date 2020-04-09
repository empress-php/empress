<?php

namespace Empress\Test\Middleware\Exception;

use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Middleware\Exception\ExceptionHandler;
use Empress\Middleware\Exception\ExceptionMappingMiddleware;
use Empress\Test\HelperTrait;
use Error;
use Exception;

class ExceptionMappingMiddlewareTest extends AsyncTestCase
{
    use HelperTrait;

    public function testHandleRequest()
    {
        $exceptionHandler = new ExceptionHandler(Exception::class, function (Context $ctx) {
            $ctx->respond('Foo');
        });

        $handler = new RequestHandler(function () {
            throw new Exception();
        });

        $middleware = new ExceptionMappingMiddleware;
        $middleware->addHandler($exceptionHandler);

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $middleware->handleRequest($request, $handler);

        $this->assertEquals('Foo', yield $response->getBody()->read());
    }

    public function testHandleUncaughtException()
    {
        $this->expectException(Error::class);

        $exceptionHandler = new ExceptionHandler(Exception::class, function () {});
        $handler = new RequestHandler(function () {
            throw new Error();
        });

        $middleware = new ExceptionMappingMiddleware;
        $middleware->addHandler($exceptionHandler);

        $request = $this->createMockRequest();

        yield $middleware->handleRequest($request, $handler);
    }

    public function testNoExceptionToCatch()
    {
        $flag = false;

        $exceptionHandler = new ExceptionHandler(Exception::class, function () use (&$flag) {
            $flag = false;
        });

        $handler = new RequestHandler(function (Context $ctx) use (&$flag) {
            $flag = true;
        });

        $middleware = new ExceptionMappingMiddleware;
        $middleware->addHandler($exceptionHandler);

        $request = $this->createMockRequest();

        yield $middleware->handleRequest($request, $handler);

        $this->assertTrue($flag);
    }
}
