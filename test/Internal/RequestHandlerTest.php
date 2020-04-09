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
        $request = $this->createMockRequest();
        $handler = new RequestHandler(function (Context $ctx) {
            $ctx->respond('Hello, World!');
        });

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, World!', yield $response->getBody()->read());
        $this->assertEquals(Status::OK, $response->getStatus());
    }

    public function testHandlerWithRequestParams()
    {
        $request = $this->createMockRequest('GET', '/', [
            'name' => 'World',
        ]);
        $handler = new RequestHandler(function (Context $ctx) {
            $name = $ctx['name'];
            $ctx->respond("Hello, $name!");
        });

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, World!', yield $response->getBody()->read());
    }
}
