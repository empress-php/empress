<?php

namespace Empress\Test\Routing\Status;

use Amp\Http\Status;
use Empress\Routing\Status\StatusHandler;
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
        $statusHandler = new StatusHandler(function () {
        }, Status::OK, $headers);
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
        $statusHandler = new StatusHandler(fn () => null, Status::OK, $headers);
        $request = $this->createMockRequest();

        $this->assertFalse($statusHandler->satisfiesHeaders($request));
    }

    public function testGetStatus()
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND);

        $this->assertEquals(Status::NOT_FOUND, $handler->getStatus());
    }

    public function testGetHeaders()
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND, [
            'X-Custom' => 'Foo',
        ]);

        $this->assertEquals([
            'X-Custom' => 'Foo',
        ], $handler->getHeaders());
    }

    public function testHasHeaders()
    {
        $handler = new StatusHandler(function () {}, Status::NOT_FOUND, [
            'X-Custom' => 'Foo',
        ]);

        $this->assertTrue($handler->hasHeaders());
    }

    public function testHasNoHeaders()
    {
        $handler = new StatusHandler(function () {}, Status::NOT_FOUND);

        $this->assertFalse($handler->hasHeaders());
    }

    public function testGetCallable()
    {
        $callable = fn() => 1;
        $handler = new StatusHandler($callable, Status::NOT_FOUND);

        $this->assertEquals($callable(), ($handler->getCallable())());
    }
}
