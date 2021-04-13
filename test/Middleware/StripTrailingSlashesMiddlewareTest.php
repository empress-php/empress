<?php

namespace Empress\Test\Middleware;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Empress\Middleware\StripTrailingSlashMiddleware;
use Empress\Test\Helper\StubRequestTrait;
use Generator;

class StripTrailingSlashesMiddlewareTest extends AsyncTestCase
{
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

        static::assertEquals('/hello', $response->getHeader('Location'));
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

    private function createDefaultMockRequestHandler(Request $request): RequestHandler
    {
        $mockRequestHandler = $this->createMock(RequestHandler::class);
        $mockRequestHandler
            ->expects(static::once())
            ->method('handleRequest')
            ->with(static::identicalTo($request))
            ->willReturn(new Success());

        return $mockRequestHandler;
    }
}