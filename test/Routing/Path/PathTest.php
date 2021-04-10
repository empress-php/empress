<?php

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testNewPath(): void
    {
        $path = new Path('/');

        static::assertEquals('/', (string) $path);
    }

    public function testToSegments(): void
    {
        $path = new Path('/hello/world');

        static::assertEquals([
            'hello',
            'world'
        ], $path->getParts());
    }

    public function testEmptyPartsAreIgnored(): void
    {
        $path = new Path('/hello//:world/*');

        static::assertEquals([
            'hello',
            ':world',
            '*'
        ], $path->getParts());
    }
}