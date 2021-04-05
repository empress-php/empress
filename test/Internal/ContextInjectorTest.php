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
        $request = $this->createMockRequest();
        $injector = new ContextInjector(fn () => null, $request);
        $response = yield $injector->inject();

        static::assertInstanceOf(Response::class, $response);
    }

    public function testInjectorWithExistingResponse()
    {
        $closure = function (Context $ctx) {
            $ctx
                ->status(Status::NOT_FOUND)
                ->response('Hello');
        };

        $request = $this->createMockRequest();
        $injector = new ContextInjector($closure, $request);

        /** @var Response $response */
        $response = yield $injector->inject();

        static::assertEquals(Status::NOT_FOUND, $response->getStatus());
        static::assertEquals('Hello', yield $response->getBody()->read());
    }

    public function testInjectorWithException()
    {
        $this->expectException(Exception::class);

        $closure = function () {
            throw new Exception();
        };

        $request = $this->createMockRequest();
        $injector = new ContextInjector($closure, $request);

        yield $injector->inject();
    }
}
