<?php

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

class PathMatcherTest extends TestCase
{
    public function testMatchSimplePath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/hello'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertTrue($matcher->matches('/hello'));
        static::assertFalse($matcher->matches('/foo'));
    }

    public function testMatchWildcardPath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/*'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertTrue($matcher->matches('/foo'));
        static::assertFalse($matcher->matches('/foo/bar'));
    }

    public function testInnerWildcardPath(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/foo/*/bar/*'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertTrue($matcher->matches('/foo/baz/bar/bazzz'));
    }

    public function testExtractNamedParams(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/greet/:name'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertEquals([
            'name' => 'Alex'
        ], $matcher->extractNamedParams('/greet/Alex'));
    }

    public function testExtractMultipleNamedParams(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/greet/:name/:lastname'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertEquals([
            'name' => 'Alex',
            'lastname' => 'Goldberg'
        ], $matcher->extractNamedParams('/greet/Alex/Goldberg'));
    }

    public function testExtractMultipleNamedParamsSeparatedByWildcard(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/users/:userId/*/:userName'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertEquals([
            'userId' => '123',
            'userName' => 'test'
        ], $matcher->extractNamedParams('/users/123/abc/test'));
    }

    public function testExtractWildcards(): void
    {
        $regexBuilder = new RegexBuilder(new Path('/start/*/*/*/stop'));
        $matcher = new PathMatcher($regexBuilder);

        static::assertEquals([
            'a',
            'b',
            'c'
        ], $matcher->extractWildcards('/start/a/b/c/stop'));
    }
}
