<?php

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

class HandlerCollectionTest extends TestCase
{
    public function testAddHandler(): void
    {
        $entry = $this->createHandlerEntry(HandlerType::GET, '/');

        $collection = new HandlerCollection();
        $collection->add($entry);

        static::assertEquals(
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

        static::assertEquals(2, $collection->filterByPath('/')->count());

        $collection->add($this->createHandlerEntry(HandlerType::DELETE, '/'));

        static::assertEquals(3, $collection->filterByPath('/')->count());
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

        static::assertEquals(3, $collection->filterByType(HandlerType::GET)->count());
        static::assertEquals(2, $collection->filterByType(HandlerType::POST)->count());
        static::assertEquals(1, $collection->filterByType(HandlerType::PATCH)->count());
    }

    public function testFirst(): void
    {
        $collection = new HandlerCollection();

        static::assertNull($collection->first());

        $collection->add($this->createHandlerEntry(HandlerType::GET, '/'));

        static::assertNotNull($collection->first());
    }

    public function testCount(): void
    {
        $collection = new HandlerCollection([]);

        static::assertEquals(0, $collection->count());

        $collection->add($this->createHandlerEntry(HandlerType::GET, '/'));

        static::assertEquals(1, $collection->count());
    }

    private function createHandlerEntry(int $type, string $path, ?callable $handler = null): HandlerEntry
    {
        return new HandlerEntry($type, new Path($path), $handler ?? fn () => null);
    }
}
