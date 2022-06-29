<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Mapping;

use Amp\Http\Status;
use Empress\Routing\Mapping\ContentTypeMatcher;
use Empress\Routing\Mapping\Status\StatusHandler;
use Empress\Test\Helper\StubRequestTrait;
use PHPUnit\Framework\TestCase;

final class ContentTypeMatcherTest extends TestCase
{
    use StubRequestTrait;

    private ContentTypeMatcher $contentTypeMatcher;

    protected function setUp(): void
    {
        $this->contentTypeMatcher = new ContentTypeMatcher();
    }

    public function testSatisfiesContentType(): void
    {
        $request = $this->createStubRequest();
        $request->setHeader('Accept', 'text/plain');

        $statusHandler = new StatusHandler(function (): void {
        }, Status::OK, 'text/plain');

        self::assertTrue($this->contentTypeMatcher->match($statusHandler, $request));
    }

    public function testDoesNotSatisfyEmptyHeaderArray(): void
    {
        $request = $this->createStubRequest();
        $request->setHeader('Accept', 'text/plain');

        $statusHandler = new StatusHandler(fn () => null, Status::OK, 'text/html');

        $request = $this->createStubRequest();

        self::assertFalse($this->contentTypeMatcher->match($statusHandler, $request));
    }
}