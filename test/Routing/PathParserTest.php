<?php

namespace Empress\Test\Routing;

use Empress\Routing\Path;
use Empress\Routing\PathParser;
use PHPUnit\Framework\TestCase;

class PathParserTest extends TestCase
{
    public function testNoMatches(): void
    {
        $path = new Path('/hello');
        $parser = new PathParser($path);

        static::assertEmpty($parser->match('/'));
    }

    public function testBothMatch(): void
    {
        $path = new Path('/hello/world');
        $parser = new PathParser($path);

        static::assertEquals([
            'hello',
            'world'
        ], $parser->match('/hello/world'));
    }

    public function testMatchSegment(): void
    {
        $path = new Path('/hello/:name');
        $parser = new PathParser($path);
        $matches = $parser->match('/hello/Jezebel');

        static::assertContains('hello', $matches);
        static::assertEquals('Jezebel', $matches['name']);
    }

    public function testMatchMultipleSegments(): void
    {
        $path = new Path('/hello/:foo/:bar');
        $parser = new PathParser($path);
        $matches = $parser->match('/hello/cheese/bacon');

        static::assertContains('hello', $matches);
        static::assertEquals('cheese', $matches['foo']);
        static::assertEquals('bacon', $matches['bar']);
    }

    public function testMatchAsterisk(): void
    {
        $path = new Path('/hello/*');
        $parser = new PathParser($path);

        static::assertEquals([
            'hello',
            'world'
        ], $parser->match('/hello/world'));
    }

    public function testMultipleAsterisks(): void
    {
        $path = new Path('/*/*/*');
        $parser = new PathParser($path);

        static::assertEquals([
            'foo',
            'bar',
            'foo-bar'
        ], $parser->match('/foo/bar/foo-bar'));
    }
}
