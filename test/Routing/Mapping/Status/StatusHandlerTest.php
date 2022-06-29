<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Mapping\Status;

use Amp\Http\Status;
use Empress\Routing\Mapping\Status\StatusHandler;
use Empress\Test\Helper\StubRequestTrait;
use PHPUnit\Framework\TestCase;

final class StatusHandlerTest extends TestCase
{
    use StubRequestTrait;

//    private ContentTypeMatcher $contentTypeMatcher;
//
//    protected function setUp(): void
//    {
//        $this->contentTypeMatcher = new ContentTypeMatcher();
//    }

//    public function testSatisfiesContentType(): void
//    {
//        $statusHandler = new StatusHandler(function (): void {
//        }, Status::OK, 'text/html');
//
//        $request = $this->createStubRequest();
//        $request->setHeaders(['Accept' => 'text/html']);
//
//        self::assertTrue($statusHandler->satisfiesContentType($request));
//    }
//
//    public function testDoesNotSatisfyEmptyHeaderArray(): void
//    {
//        $statusHandler = new StatusHandler(fn () => null, Status::OK, [
//            'X-Custom-1' => 'foo',
//            'X-Custom-2' => 'bar',
//        ]);
//
//        $request = $this->createStubRequest();
//
//        self::assertFalse($statusHandler->satisfiesContentType($request));
//    }

    public function testGetStatus(): void
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND);

        self::assertSame(Status::NOT_FOUND, $handler->getStatus());
    }

    public function testGetHeaders(): void
    {
        $handler = new StatusHandler(fn () => null, Status::NOT_FOUND, 'text/html');

        self::assertSame('text/html', $handler->getContentType());
    }

    public function testHasContentType(): void
    {
        $handler = new StatusHandler(function (): void {
        }, Status::NOT_FOUND, 'application/json');

        self::assertTrue($handler->hasContentType());
    }

    public function testHasNoHeaders(): void
    {
        $handler = new StatusHandler(function (): void {
        }, Status::NOT_FOUND);

        self::assertFalse($handler->hasContentType());
    }

    public function testGetCallable(): void
    {
        $closure = fn () => 1;
        $handler = new StatusHandler($closure, Status::NOT_FOUND);

        self::assertSame($closure(), ($handler->getCallable())());
    }
}
