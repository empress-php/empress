<?php

namespace Empress\Test\Internal;

use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\ContextInjector;
use Empress\Test\Helper\StubRequestTrait;
use Exception;
use Generator;

class ContextInjectorTest extends AsyncTestCase
{
    use StubRequestTrait;

    public function testInjectorWithExistingResponse(): Generator
    {
        $request = $this->createStubRequest();
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

    public function testInjectorWithException(): Generator
    {
        $this->expectException(Exception::class);

        $request = $this->createStubRequest();
        $context = new Context($request);
        $injector = new ContextInjector($context);

        yield $injector->inject(fn () => throw new Exception());
    }
}
