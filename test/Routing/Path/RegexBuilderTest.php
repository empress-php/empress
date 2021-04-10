<?php

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

class RegexBuilderTest extends TestCase
{
    public function testPlainPath(): void
    {
        $path = new Path('/hello/world');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/hello/world/?$~', $builder->getRegex());
    }

    public function testSingleWildcardPath(): void
    {
        $path = new Path('/hello/*');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/hello/(.+?)/?$~', $builder->getRegex());
    }

    public function testRootWildcardPath(): void
    {
        $path = new Path('/*');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/(.+?)/?$~', $builder->getRegex());
    }

    public function testMultipleWildcardPaths(): void
    {
        $path = new Path('/foo/*/bar/*/baz');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/foo/(.+?)/bar/(.+?)/baz/?$~', $builder->getRegex());
    }

    public function testSingleParam(): void
    {
        $path = new Path('/foo/:name/123');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/foo/(?<name>.+?)/123/?$~', $builder->getRegex());
    }

    public function testMultipleParams(): void
    {
        $path = new Path('/:age/:name/xyz/:x');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/(?<age>.+?)/(?<name>.+?)/xyz/(?<x>.+?)/?$~', $builder->getRegex());
    }

    public function testMixedPath(): void
    {
        $path = new Path('/*/php/*/:abc/:z');
        $builder = new RegexBuilder($path);

        static::assertEquals('~^/(.+?)/php/(.+?)/(?<abc>.+?)/(?<z>.+?)/?$~', $builder->getRegex());
    }
}
