<?php

namespace Empress\Test\Internal;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Internal\RequestHandler;
use Empress\RequestContext;
use Empress\Test\HelperTrait;
use Empress\Transformer\JsonTransformer;

class RequestHandlerTest extends AsyncTestCase
{
    use HelperTrait;

    public function testPlainResponse()
    {
        $closure = function () {
            return new Response(Status::OK, [], 'Hello, World!');
        };

        $request = $this->createMockRequest('GET', '/');
        $handler = new RequestHandler($closure);

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, World!', yield $response->getBody()->read());
        $this->assertEquals(Status::OK, $response->getStatus());
    }

    public function testResponseWithTransformer()
    {
        $closure = function () {
            return ['status' => 'ok'];
        };

        $request = $this->createMockRequest('GET', '/');
        $handler = new RequestHandler($closure, new JsonTransformer());

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals(\json_encode(['status' => 'ok']), yield $response->getBody()->read());
    }

    public function testHandlerWithRequestParams()
    {
        $closure = function ($request) {

            /** @var RequestContext $request */
            $name = $request->getParam('name');
            return new Response(Status::OK, [], "Hello, $name");
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
