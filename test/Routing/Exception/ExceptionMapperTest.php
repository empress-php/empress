<?php

namespace Empress\Test\Routing\Exception;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\ContextInjector;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Test\Helper\StubRequestTrait;
use Error;
use Exception;

class ExceptionMapperTest extends AsyncTestCase
{
    use StubRequestTrait;

    public function testHandleRequest()
    {
        $mapper = new ExceptionMapper();
        $mapper->addHandler(new ExceptionHandler(function (Context $ctx) {
            $ctx->response('Foo');
        }, Exception::class));

        $request = $this->createStubRequest();

        $context = new Context($request);
        $injector = new ContextInjector($context);
        $injector->setThrowable(new Exception());

        yield $mapper->process($injector);

        static::assertEquals('Foo', yield $injector->getResponse()->getBody()->read());
    }

    public function testHandleUncaughtException()
    {
        $this->expectException(Error::class);

        $mapper = new ExceptionMapper();
        $mapper->addHandler(new ExceptionHandler(Exception::class, fn () => null));

        $request = $this->createStubRequest();

        $context = new Context($request);
        $injector = new ContextInjector($context);
        $injector->setThrowable(new Error());

        yield $mapper->process($injector);
    }
}
