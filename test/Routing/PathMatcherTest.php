<?php

namespace Empress\Test\Routing;

use Empress\Routing\HandlerEntry;
use Empress\Routing\HandlerType;
use Empress\Routing\Path;
use Empress\Routing\PathMatcher;
use PHPUnit\Framework\TestCase;

class PathMatcherTest extends TestCase
{
    public function testHasEntries(): void
    {
        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        static::assertTrue($matcher->hasEntries());
    }

    public function testSuccessfulMerge(): void
    {
        $this->expectNotToPerformAssertions();

        $closure = fn () => null;
        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), $closure));

        $sourceMatcher = new PathMatcher();
        $sourceMatcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/hello'), $closure));

        $matcher->merge($sourceMatcher);
    }

    public function testFailedMerge(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $sourceMatcher = new PathMatcher();
        $sourceMatcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $matcher->merge($sourceMatcher);
    }

    public function testFindEntries(): void
    {
        $closure = fn () => null;
        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::POST, new Path('*'), $closure));
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/foo'), $closure));

        $entries = $matcher->findEntries('/foo');
        $entry = \reset($entries);

        static::assertEquals(HandlerType::POST, $entry->getType());
        static::assertEquals(new Path('*'), $entry->getPath());
        static::assertEquals($closure, $entry->getHandler());
    }

    public function testNoEntriesFound(): void
    {
        $matcher = new PathMatcher();

        static::assertEmpty($matcher->findEntries('/'));
    }

    public function testFindEntriesWithSamePathsButDifferentTypes(): void
    {
        $closure = fn () => null;
        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), $closure));
        $matcher->addEntry(new HandlerEntry(HandlerType::POST, new Path('/'), $closure));

        $entries = $matcher->findEntries('/');

        static::assertCount(2, $entries);
    }

    public function testGetEntries(): void
    {
        $matcher = new PathMatcher();

        static::assertCount(0, $matcher->getEntries());

        $matcher->addEntry(new HandlerEntry(HandlerType::BEFORE, new Path('*'), fn () => null));

        static::assertCount(1, $matcher->getEntries());
    }

    public function testGetPathParams()
    {
        $matcher = new PathMatcher();
        $matcher->addEntry($handler1 = new HandlerEntry(HandlerType::GET, new Path('/hello/:bar/foo'), fn () => null));
        $matcher->addEntry($handler2 = new HandlerEntry(HandlerType::POST, new Path('/:name'), fn () => null));

        static::assertEquals([
            'bar' => 'bazzz'
        ], $matcher->getPathParams($handler1, '/hello/bazzz/foo'));

        static::assertEquals([
            'name' => 'Jezebel'
        ], $matcher->getPathParams($handler2, '/Jezebel'));
    }
}
