<?php

namespace Empress\Test\Middleware;

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Middleware\DefaultHeadersMiddleware;
use Empress\Test\HelperTrait;

class DefaultHeadersMiddlewareTest extends AsyncTestCase
{
    use HelperTrait;

    public function testHandleRequest()
    {
        $headers = [
            'x-Custom-1' => 'some value',
            'x-Custom-2' => 'some other value',
        ];

        $request = $this->createMockRequest('GET', '/');
        $handler = new CallableRequestHandler(function () {
            return new Response();
        });
        $middleware = new DefaultHeadersMiddleware($headers);

        /** @var Response $response */
        $response = yield $middleware->handleRequest($request, $handler);

        foreach ($headers as $name => $value) {
            $this->assertNotContains($name, $response->getHeaders());
            $this->assertEquals($value, $response->getHeader($name));
        }
    }
}
