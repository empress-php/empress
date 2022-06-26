<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function testNewPath(): void
    {
        $path = new Path('/');

        self::assertSame('/', (string) $path);
    }

    public function testToSegments(): void
    {
        $path = new Path('/hello/world');

        self::assertSame([
            'hello',
            'world',
        ], $path->getParts());
    }

    public function testEmptyPartsAreIgnored(): void
    {
        $path = new Path('/hello//:world/*');

        self::assertSame([
            'hello',
            ':world',
            '*',
        ], $path->getParts());
    }
}
