<?php

namespace Empress\Test\Internal;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\ContextInjector;
use Empress\Test\Helper\MockRequestTrait;
use Exception;

class ContextInjectorTest extends AsyncTestCase
{
    use MockRequestTrait;

    public function testInjectorWithNewResponse()
    {
        $request = $this->createMockRequest();
        $context = new Context($request);
        $injector = new ContextInjector($context);

        yield $injector->inject(fn () => null);

        static::assertInstanceOf(Response::class, $injector->getResponse());
    }

    public function testInjectorWithExistingResponse()
    {
        $request = $this->createMockRequest();
        $context = new Context($request);
        $injector = new ContextInjector($context);

        yield $injector->inject(function (Context $ctx) {
            $ctx
                ->status(Status::NOT_FOUND)
                ->response('Hello');
        });

        static::assertEquals(Status::NOT_FOUND, $injector->getResponse()->getStatus());
        static::assertEquals('Hello', yield $injector->getResponse()->getBody()->read());
    }

    public function testInjectorWithException()
    {
        $this->expectException(Exception::class);

        $request = $this->createMockRequest();
        $context = new Context($request);
        $injector = new ContextInjector($context);

        yield $injector->inject(function () {
            throw new Exception();
        });
    }
}
