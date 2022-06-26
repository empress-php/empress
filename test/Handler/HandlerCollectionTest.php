<?php

declare(strict_types=1);

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerTypeEnum;
use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

final class HandlerCollectionTest extends TestCase
{
    public function testAddHandler(): void
    {
        $entry = $this->createHandlerEntry(HandlerTypeEnum::GET, '/');

        $collection = new HandlerCollection();
        $collection->add($entry);

        self::assertSame(
            $entry,
            $collection
                ->filterByPath('/')
                ->filterByType(HandlerTypeEnum::GET)
                ->first()
        );
    }

    public function testFindByPath(): void
    {
        $entries = [
            $this->createHandlerEntry(HandlerTypeEnum::GET, '/'),
            $this->createHandlerEntry(HandlerTypeEnum::POST, '/'),
        ];

        $collection = new HandlerCollection($entries);

        self::assertSame(2, $collection->filterByPath('/')->count());

        $collection->add($this->createHandlerEntry(HandlerTypeEnum::DELETE, '/'));

        self::assertSame(3, $collection->filterByPath('/')->count());
    }

    public function testFilterByType(): void
    {
        $entries = [
            $this->createHandlerEntry(HandlerTypeEnum::GET, '/'),
            $this->createHandlerEntry(HandlerTypeEnum::GET, '/hello'),
            $this->createHandlerEntry(HandlerTypeEnum::GET, '/foo'),
            $this->createHandlerEntry(HandlerTypeEnum::POST, '/baz'),
            $this->createHandlerEntry(HandlerTypeEnum::POST, '/bar'),
            $this->createHandlerEntry(HandlerTypeEnum::PATCH, '/xyz'),
        ];

        $collection = new HandlerCollection($entries);

        self::assertSame(3, $collection->filterByType(HandlerTypeEnum::GET)->count());
        self::assertSame(2, $collection->filterByType(HandlerTypeEnum::POST)->count());
        self::assertSame(1, $collection->filterByType(HandlerTypeEnum::PATCH)->count());
    }

    public function testFirst(): void
    {
        $collection = new HandlerCollection();

        self::assertNull($collection->first());

        $collection->add($this->createHandlerEntry(HandlerTypeEnum::GET, '/'));

        self::assertNotNull($collection->first());
    }

    public function testCount(): void
    {
        $collection = new HandlerCollection([]);

        self::assertSame(0, $collection->count());

        $collection->add($this->createHandlerEntry(HandlerTypeEnum::GET, '/'));

        self::assertSame(1, $collection->count());
    }

    private function createHandlerEntry(HandlerTypeEnum $type, string $path, ?callable $handler = null): HandlerEntry
    {
        return new HandlerEntry($type, new Path($path), $handler ?? fn () => null);
    }
}
