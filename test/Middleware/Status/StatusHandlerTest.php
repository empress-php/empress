<?php

namespace Empress\Test\Internal;

use Amp\Http\Status;
use Empress\Middleware\Status\StatusHandler;
use Empress\Test\HelperTrait;
use PHPUnit\Framework\TestCase;

class StatusHandlerTest extends TestCase
{
    use HelperTrait;

    public function testSatisfiesHeaders()
    {
        $headers = [
            'X-Custom-1' => 'foo',
            'X-Custom-2' => 'bar',
        ];
        $statusHandler = new StatusHandler(Status::OK, function () {}, $headers);
        $request = $this->createMockRequest();
        $request->setHeaders($headers);

        $this->assertTrue($statusHandler->satisfiesHeaders($request));
    }

    public function testDoesNotSatisfyEmptyHeaderArray()
    {
        $headers = [
            'X-Custom-1' => 'foo',
            'X-Custom-2' => 'bar',
        ];
        $statusHandler = new StatusHandler(Status::OK, function () {}, $headers);
        $request = $this->createMockRequest();

        $this->assertFalse($statusHandler->satisfiesHeaders($request));
    }

    public function testGetStatus()
    {
        $handler = new StatusHandler(Status::NOT_FOUND, function () {});

        $this->assertEquals(Status::NOT_FOUND, $handler->getStatus());
    }

    public function testGetHeaders()
    {
        $handler = new StatusHandler(Status::NOT_FOUND, function () {}, [
            'X-Custom' => 'Foo',
        ]);

        $this->assertEquals([
            'X-Custom' => 'Foo',
        ], $handler->getHeaders());
    }

    public function testHasHeaders()
    {
        $handler = new StatusHandler(Status::NOT_FOUND, function () {}, [
            'X-Custom' => 'Foo',
        ]);

        $this->assertTrue($handler->hasHeaders());
    }

    public function testHasNoHeaders()
    {
        $handler = new StatusHandler(Status::NOT_FOUND, function () {});

        $this->assertFalse($handler->hasHeaders());
    }

    public function testGetCallable()
    {
        $callable = function () {
            return 1;
        };

        $handler = new StatusHandler(Status::NOT_FOUND, $callable);

        $this->assertEquals($callable(), ($handler->getCallable())());
    }
}
