<?php

namespace Empress\Test\Internal;

use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Internal\RequestHandler;
use Empress\Transformer\JsonTransformer;
use League\Uri\Http;

class RequestHandlerTest extends AsyncTestCase
{
    public function testPlainResponse()
    {
        $closure = function () {
            return new Response(Status::OK, [], 'Hello, World!');
        };

        $client = $this->createMock(Client::class);
        $request = new Request($client, 'GET', Http::createFromString('/'));

        // Request object is router-aware
        $request->setAttribute(Router::class, null);

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

        $client = $this->createMock(Client::class);
        $request = new Request($client, 'GET', Http::createFromString('/'));

        // Request object is router-aware
        $request->setAttribute(Router::class, null);

        $handler = new RequestHandler($closure, new JsonTransformer());

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals(\json_encode(['status' => 'ok']), yield $response->getBody()->read());
    }

    public function testHandlerWithRequestParams()
    {
        $closure = function ($request) {
            $name = $request->getParam('name');
            return new Response(Status::OK, [], "Hello, $name");
        };

        $client = $this->createMock(Client::class);
        $request = new Request($client, 'GET', Http::createFromString('/'));

        // Mock request params
        $request->setAttribute(Router::class, [
            'name' => 'Jakob',
        ]);

        $handler = new RequestHandler($closure);

        /** @var Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals('Hello, Jakob', yield $response->getBody()->read());
    }
}
