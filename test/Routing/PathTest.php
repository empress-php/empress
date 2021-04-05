<?php

namespace Empress\Test\Routing;

use Empress\Exception\RouteException;
use Empress\Routing\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testSingleSegment()
    {
        $path = new Path('/hello');
        $segments = $path->toSegments();

        static::assertEquals([
            'hello'
        ], $segments);
    }

    public function testTwoSegments()
    {
        $path = new Path('/hello/world');
        $segments = $path->toSegments();

        static::assertEquals([
            'hello',
            'world'
        ], $segments);
    }

    public function testMultipleSlashes()
    {
        $path = new Path('//hello');
        $segments = $path->toSegments();

        static::assertEquals([
            'hello'
        ], $segments);

        $path = new Path('/hello//world');
        $segments = $path->toSegments();

        static::assertEquals([
            'hello',
            'world'
        ], $segments);
    }

    public function testMultipleSlashesOnEnd()
    {
        $path = new Path('/hello//');
        $segments = $path->toSegments();

        static::assertEquals([
            'hello'
        ], $segments);
    }

    public function testNoEmptySegmentsAllowed()
    {
        $this->expectException(RouteException::class);

        (new Path('/hello/:/world'))->toSegments();
    }
}
