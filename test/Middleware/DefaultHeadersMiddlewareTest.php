<?php

namespace Empress\Test\Middleware;

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Middleware\DefaultHeadersMiddleware;
use Empress\Test\Helper\StubRequestTrait;
use Generator;

class DefaultHeadersMiddlewareTest extends AsyncTestCase
{
    use StubRequestTrait;

    public function testHandleRequest(): Generator
    {
        $headers = [
            'x-Custom-1' => 'some value',
            'x-Custom-2' => 'some other value',
        ];

        $request = $this->createStubRequest();
        $handler = new CallableRequestHandler(fn () => new Response());
        $middleware = new DefaultHeadersMiddleware($headers);

        /** @var Response $response */
        $response = yield $middleware->handleRequest($request, $handler);

        foreach ($headers as $name => $value) {
            static::assertNotContains($name, $response->getHeaders());
            static::assertEquals($value, $response->getHeader($name));
        }
    }
}
