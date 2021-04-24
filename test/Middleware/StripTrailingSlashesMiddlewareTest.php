<?php

namespace Empress\Test\Middleware;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Middleware\StripTrailingSlashMiddleware;
use Empress\Test\Helper\MockRequestHandlerTrait;
use Empress\Test\Helper\StubRequestTrait;
use Generator;

class StripTrailingSlashesMiddlewareTest extends AsyncTestCase
{
    use MockRequestHandlerTrait;
    use StubRequestTrait;

    public function testHandleRequestWithRootPath(): Generator
    {
        $request = $this->createStubRequest('GET', '/hello');
        $middleware = new StripTrailingSlashMiddleware();

        $mockRequestHandler = $this->createDefaultMockRequestHandler($request);

        yield $middleware->handleRequest($request, $mockRequestHandler);
    }

    public function testHandleRequestWithNoTrailingSlash(): Generator
    {
        $request = $this->createStubRequest('GET', '/hello');
        $middleware = new StripTrailingSlashMiddleware();

        $mockRequestHandler = $this->createDefaultMockRequestHandler($request);

        yield $middleware->handleRequest($request, $mockRequestHandler);
    }

    public function testRedirectForGetRequest(): Generator
    {
        $request = $this->createStubRequest('GET', '/hello/');
        $requestHandler = $this->createMock(RequestHandler::class);
        $middleware = new StripTrailingSlashMiddleware();

        /** @var Response $response */
        $response = yield $middleware->handleRequest($request, $requestHandler);

        static::assertEquals('//example.com:1234/hello', $response->getHeader('Location'));
        static::assertEquals(Status::PERMANENT_REDIRECT, $response->getStatus());
    }

    public function testNoRedirectForPostRequest(): Generator
    {
        $request = $this->createStubRequest('POST', '/hello/');
        $requestHandler = $this->createDefaultMockRequestHandler($request);
        $middleware = new StripTrailingSlashMiddleware();

        yield $middleware->handleRequest($request, $requestHandler);

        static::assertEquals('/hello', $request->getUri()->getPath());
    }
}
