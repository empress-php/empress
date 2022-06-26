<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Status;

use Amp\Http\Status;
use Empress\Routing\Status\StatusHandler;
use Empress\Test\Helper\StubRequestTrait;
use PHPUnit\Framework\TestCase;

final class StatusHandlerTest extends TestCase
{
    use StubRequestTrait;

    public function testSatisfiesHeaders(): void
    {
        $headers = [
            'X-Custom-1' => 'foo',
            'X-Custom-2' => 'bar',
        ];

        $statusHandler = new StatusHandler(function (): void {
        }, Status::OK, $headers);

        $request = $this->createStubRequest();
        $request->setHeaders($headers);

        self::assertTrue($statusHandler->satisfiesHeaders($request));
    }

    public function testDoesNotSatisfyEmptyHeaderArray(): void
    {
        $statusHandler = new StatusHandler(fn () => null, Status::OK, [
            'X-Custom-1' => 'foo',
            'X-Custom-2' => 'bar',
        ]);

        $request = $this->createStubRequest();

        self::assertFalse($statusHandler->satisfiesHeaders($request));
    }

    public function testGetStatus(): void
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND);

        self::assertSame(Status::NOT_FOUND, $handler->getStatus());
    }

    public function testGetHeaders(): void
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND, [
            'X-Custom' => 'Foo',
        ]);

        self::assertSame([
            'X-Custom' => 'Foo',
        ], $handler->getHeaders());
    }

    public function testHasHeaders(): void
    {
        $handler = new StatusHandler(function (): void {
        }, Status::NOT_FOUND, [
            'X-Custom' => 'Foo',
        ]);

        self::assertTrue($handler->hasHeaders());
    }

    public function testHasNoHeaders(): void
    {
        $handler = new StatusHandler(function (): void {
        }, Status::NOT_FOUND);

        self::assertFalse($handler->hasHeaders());
    }

    public function testGetCallable(): void
    {
        $closure = fn () => 1;
        $handler = new StatusHandler($closure, Status::NOT_FOUND);

        self::assertSame($closure(), ($handler->getCallable())());
    }
}
