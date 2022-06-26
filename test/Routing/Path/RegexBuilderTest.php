<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

final class RegexBuilderTest extends TestCase
{
    private RegexBuilder $regexBuilder;

    protected function setUp(): void
    {
        $this->regexBuilder = new RegexBuilder();
    }

    public function testPlainPath(): void
    {
        $path = new Path('/hello/world');

        self::assertSame('~^/hello/world/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testSingleWildcardPath(): void
    {
        $path = new Path('/hello/*');

        self::assertSame('~^/hello/([^/]*)/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testRootWildcardPath(): void
    {
        $path = new Path('/*');

        self::assertSame('~^/([^/]*)/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testMultipleWildcardPaths(): void
    {
        $path = new Path('/foo/*/bar/*/baz');

        self::assertSame('~^/foo/([^/]*)/bar/([^/]*)/baz/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testSingleParam(): void
    {
        $path = new Path('/foo/:name/123');

        self::assertSame('~^/foo/(?<name>.+?)/123/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testMultipleParams(): void
    {
        $path = new Path('/:age/:name/xyz/:x');

        self::assertSame('~^/(?<age>.+?)/(?<name>.+?)/xyz/(?<x>.+?)/?$~', $this->regexBuilder->buildRegex($path));
    }

    public function testMixedPath(): void
    {
        $path = new Path('/*/php/*/:abc/:z');

        self::assertSame('~^/([^/]*)/php/([^/]*)/(?<abc>.+?)/(?<z>.+?)/?$~', $this->regexBuilder->buildRegex($path));
    }
}
