<?php

declare(strict_types=1);

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

final class HandlerCollectionTest extends TestCase
{
    public function testAddHandler(): void
    {
        $entry = $this->createHandlerEntry(HandlerType::GET, '/');

        $collection = new HandlerCollection();
        $collection->add($entry);

        self::assertSame(
            $entry,
            $collection
                ->filterByPath('/')
                ->filterByType(HandlerType::GET)
                ->first()
        );
    }

    public function testFindByPath(): void
    {
        $entries = [
            $this->createHandlerEntry(HandlerType::GET, '/'),
            $this->createHandlerEntry(HandlerType::POST, '/'),
        ];

        $collection = new HandlerCollection($entries);

        self::assertSame(2, $collection->filterByPath('/')->count());

        $collection->add($this->createHandlerEntry(HandlerType::DELETE, '/'));

        self::assertSame(3, $collection->filterByPath('/')->count());
    }

    public function testFilterByType(): void
    {
        $entries = [
            $this->createHandlerEntry(HandlerType::GET, '/'),
            $this->createHandlerEntry(HandlerType::GET, '/hello'),
            $this->createHandlerEntry(HandlerType::GET, '/foo'),
            $this->createHandlerEntry(HandlerType::POST, '/baz'),
            $this->createHandlerEntry(HandlerType::POST, '/bar'),
            $this->createHandlerEntry(HandlerType::PATCH, '/xyz'),
        ];

        $collection = new HandlerCollection($entries);

        self::assertSame(3, $collection->filterByType(HandlerType::GET)->count());
        self::assertSame(2, $collection->filterByType(HandlerType::POST)->count());
        self::assertSame(1, $collection->filterByType(HandlerType::PATCH)->count());
    }

    public function testFirst(): void
    {
        $collection = new HandlerCollection();

        self::assertNull($collection->first());

        $collection->add($this->createHandlerEntry(HandlerType::GET, '/'));

        self::assertNotNull($collection->first());
    }

    public function testCount(): void
    {
        $collection = new HandlerCollection([]);

        self::assertSame(0, $collection->count());

        $collection->add($this->createHandlerEntry(HandlerType::GET, '/'));

        self::assertSame(1, $collection->count());
    }

    private function createHandlerEntry(int $type, string $path, ?callable $handler = null): HandlerEntry
    {
        return new HandlerEntry($type, new Path($path), $handler ?? fn () => null);
    }
}
