<?php

namespace Empress\Test\Internal;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\RequestHandler;
use Empress\Test\HelperTrait;

class RequestHandlerTest extends AsyncTestCase
{
    use HelperTrait;

    public function testPlainResponse()
    {
        $closure = function (Context $ctx) {
            $ctx->respond('Hello, World!');
        };

        $request = $this->createMockRequest('GET', '/');
        $handler = new RequestHandler($closure);

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, World!', yield $response->getBody()->read());
        $this->assertEquals(Status::OK, $response->getStatus());
    }

    public function testHandlerWithRequestParams()
    {
        $closure = function (Context $ctx) {
            $name = $ctx['name'];
            $ctx->respond("Hello, $name");
        };

        $request = $this->createMockRequest('GET', '/', [
            'name' => 'Jakob',
        ]);
        $handler = new RequestHandler($closure);

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, Jakob', yield $response->getBody()->read());
    }
}
