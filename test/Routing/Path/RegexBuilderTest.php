<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

final class RegexBuilderTest extends TestCase
{
    public function testPlainPath(): void
    {
        $path = new Path('/hello/world');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/hello/world/?$~', $builder->getRegex());
    }

    public function testSingleWildcardPath(): void
    {
        $path = new Path('/hello/*');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/hello/([^/]*)/?$~', $builder->getRegex());
    }

    public function testRootWildcardPath(): void
    {
        $path = new Path('/*');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/([^/]*)/?$~', $builder->getRegex());
    }

    public function testMultipleWildcardPaths(): void
    {
        $path = new Path('/foo/*/bar/*/baz');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/foo/([^/]*)/bar/([^/]*)/baz/?$~', $builder->getRegex());
    }

    public function testSingleParam(): void
    {
        $path = new Path('/foo/:name/123');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/foo/(?<name>.+?)/123/?$~', $builder->getRegex());
    }

    public function testMultipleParams(): void
    {
        $path = new Path('/:age/:name/xyz/:x');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/(?<age>.+?)/(?<name>.+?)/xyz/(?<x>.+?)/?$~', $builder->getRegex());
    }

    public function testMixedPath(): void
    {
        $path = new Path('/*/php/*/:abc/:z');
        $builder = new RegexBuilder($path);

        self::assertSame('~^/([^/]*)/php/([^/]*)/(?<abc>.+?)/(?<z>.+?)/?$~', $builder->getRegex());
    }
}
