<?php

namespace Empress\Test\Routing\Exception;

use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Test\HelperTrait;
use Error;
use Exception;

class ExceptionMapperTest extends AsyncTestCase
{
    use HelperTrait;

    public function testHandleRequest()
    {
        $mapper = new ExceptionMapper();
        $mapper->addHandler(new ExceptionHandler(function (Context $ctx) {
            $ctx->response('Foo');
        }, Exception::class));

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $mapper->process(new Exception(), $request);

        static::assertEquals('Foo', yield $response->getBody()->read());
    }

    public function testHandleUncaughtException()
    {
        $this->expectException(Error::class);

        $mapper = new ExceptionMapper();
        $mapper->addHandler(new ExceptionHandler(Exception::class, fn () => null));

        $request = $this->createMockRequest();

        yield $mapper->process(new Error(), $request);
    }
}
