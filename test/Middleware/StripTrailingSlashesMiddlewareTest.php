<?php

declare(strict_types=1);

namespace Empress\Test\Middleware;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Middleware\StripTrailingSlashMiddleware;
use Empress\Test\Helper\MockRequestHandlerTrait;
use Empress\Test\Helper\StubRequestTrait;
use Generator;

final class StripTrailingSlashesMiddlewareTest extends AsyncTestCase
{
    use MockRequestHandlerTrait;
    use StubRequestTrait;

    private StripTrailingSlashMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new StripTrailingSlashMiddleware();

        parent::setUp();
    }

    public function testHandleRequestWithNoTrailingSlash(): Generator
    {
        $request = $this->createStubRequest('GET', '/hello');

        $mockRequestHandler = $this->createDefaultMockRequestHandler($request);

        yield $this->middleware->handleRequest($request, $mockRequestHandler);
    }

    public function testRedirectForGetRequest(): Generator
    {
        $request = $this->createStubRequest('GET', '/hello/');
        $requestHandler = $this->createMock(RequestHandler::class);

        /** @var Response $response */
        $response = yield $this->middleware->handleRequest($request, $requestHandler);

        self::assertSame('//example.com:1234/hello', $response->getHeader('Location'));
        self::assertSame(Status::PERMANENT_REDIRECT, $response->getStatus());
    }

    public function testNoRedirectForPostRequest(): Generator
    {
        $request = $this->createStubRequest('POST', '/hello/');
        $requestHandler = $this->createDefaultMockRequestHandler($request);

        yield $this->middleware->handleRequest($request, $requestHandler);

        self::assertSame('/hello', $request->getUri()->getPath());
    }
}
