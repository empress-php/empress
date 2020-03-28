<?php

namespace Empress\Test\Internal;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\ContextInjector;
use Empress\Test\HelperTrait;
use Exception;

class ContextInjectorTest extends AsyncTestCase
{
    use HelperTrait;

    public function testInjectorWithNewResponse()
    {
        $closure = function () {
        };

        $request = $this->createMockRequest('GET', '/');
        $injector = new ContextInjector($closure, $request);
        $response = yield $injector->inject();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInjectorWithExistingResponse()
    {
        $closure = function (Context $ctx) {
            $ctx
                ->status(Status::NOT_FOUND)
                ->respond('Hello');
        };

        $request = $this->createMockRequest('GET', '/');
        $injector = new ContextInjector($closure, $request);

        /** @var Response $response */
        $response = yield $injector->inject();

        $this->assertEquals(Status::NOT_FOUND, $response->getStatus());
        $this->assertEquals('Hello', yield $response->getBody()->read());
    }

    public function testInjectorWithException()
    {
        $this->expectException(Exception::class);

        $closure = function () {
            throw new Exception();
        };

        $request = $this->createMockRequest('GET', '/');
        $injector = new ContextInjector($closure, $request);

        yield $injector->inject();
    }
}
