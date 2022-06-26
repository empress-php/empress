<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

final class PathMatcherTest extends TestCase
{
    public function testMatchSimplePath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/hello'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertTrue($matcher->matches('/hello'));
        self::assertFalse($matcher->matches('/foo'));
    }

    public function testMatchWildcardPath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/*'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertTrue($matcher->matches('/foo'));
        self::assertFalse($matcher->matches('/foo/bar'));
    }

    public function testInnerWildcardPath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/foo/*/bar/*'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertTrue($matcher->matches('/foo/baz/bar/bazzz'));
    }

    public function testExtractNamedParams(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/greet/:name'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertSame([
            'name' => 'Alex',
        ], $matcher->extractNamedParams('/greet/Alex'));
    }

    public function testExtractMultipleNamedParams(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/greet/:name/:lastname'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertSame([
            'name' => 'Alex',
            'lastname' => 'Goldberg',
        ], $matcher->extractNamedParams('/greet/Alex/Goldberg'));
    }

    public function testExtractMultipleNamedParamsSeparatedByWildcard(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/users/:userId/*/:userName'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertSame([
            'userId' => '123',
            'userName' => 'test',
        ], $matcher->extractNamedParams('/users/123/abc/test'));
    }

    public function testExtractWildcards(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/start/*/*/*/stop'));
        $matcher = new PathMatcher($regexBuilder);

        self::assertSame([
            'a',
            'b',
            'c',
        ], $matcher->extractWildcards('/start/a/b/c/stop'));
    }
}
