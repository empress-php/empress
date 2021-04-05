<?php

namespace Empress\Test\Middleware;

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Middleware\DefaultHeadersMiddleware;
use Empress\Test\Helper\MockRequestTrait;

class DefaultHeadersMiddlewareTest extends AsyncTestCase
{
    use MockRequestTrait;

    public function testHandleRequest()
    {
        $headers = [
            'x-Custom-1' => 'some value',
            'x-Custom-2' => 'some other value',
        ];

        $request = $this->createMockRequest();
        $handler = new CallableRequestHandler(function () {
            return new Response();
        });
        $middleware = new DefaultHeadersMiddleware($headers);

        /** @var Response $response */
        $response = yield $middleware->handleRequest($request, $handler);

        foreach ($headers as $name => $value) {
            static::assertNotContains($name, $response->getHeaders());
            static::assertEquals($value, $response->getHeader($name));
        }
    }
}
