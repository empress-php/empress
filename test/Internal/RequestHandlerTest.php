<?php

namespace Empress\Test\Internal;

use Amp\Promise;
use Amp\Success;
use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Internal\RequestHandler;
use Empress\JsonTransformer;
use Empress\ResponseTransformerInterface;
use League\Uri\Http;
use function Amp\call;

class RequestHandlerTest extends AsyncTestCase
{
    public function testPlainResponse()
    {
        $closure = function () {
            return new Response(Status::OK, [], 'Hello, World!');
        };

        $client = $this->createMock(Client::class);
        $request = new Request($client, 'GET', Http::createFromString('/'));

        $request->setAttribute(Router::class, null);

        $handler = new RequestHandler($closure);

        /** @var \Amp\Http\Server\Response $response */
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

        $request->setAttribute(Router::class, null);

        $handler = new RequestHandler($closure, new JsonTransformer());

        /** @var \Amp\Http\Server\Response $response */
        $response = yield $handler->handleRequest($request);

        $this->assertEquals(json_encode(['status' => 'ok']), yield $response->getBody()->read());
    }
}
