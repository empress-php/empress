<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Path;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;
use PHPUnit\Framework\TestCase;

final class PathMatcherTest extends TestCase
{
    private RegexBuilder $regexBuilder;

    protected function setUp(): void
    {
        $this->regexBuilder = new RegexBuilder();
    }

    public function testMatchSimplePath(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/hello'));
        $matcher = new PathMatcher($regex);

        self::assertTrue($matcher->matches('/hello'));
        self::assertFalse($matcher->matches('/foo'));
    }

    public function testMatchWildcardPath(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/*'));
        $matcher = new PathMatcher($regex);

        self::assertTrue($matcher->matches('/foo'));
        self::assertFalse($matcher->matches('/foo/bar'));
    }

    public function testInnerWildcardPath(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/foo/*/bar/*'));
        $matcher = new PathMatcher($regex);

        self::assertTrue($matcher->matches('/foo/baz/bar/bazzz'));
    }

    public function testExtractNamedParams(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/greet/:name'));
        $matcher = new PathMatcher($regex);

        self::assertSame([
            'name' => 'Alex',
        ], $matcher->extractNamedParams('/greet/Alex'));
    }

    public function testExtractMultipleNamedParams(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/greet/:name/:lastname'));
        $matcher = new PathMatcher($regex);

        self::assertSame([
            'name' => 'Alex',
            'lastname' => 'Goldberg',
        ], $matcher->extractNamedParams('/greet/Alex/Goldberg'));
    }

    public function testExtractMultipleNamedParamsSeparatedByWildcard(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/users/:userId/*/:userName'));
        $matcher = new PathMatcher($regex);

        self::assertSame([
            'userId' => '123',
            'userName' => 'test',
        ], $matcher->extractNamedParams('/users/123/abc/test'));
    }

    public function testExtractWildcards(): void
    {
        $regex = $this->regexBuilder->buildRegex(new Path('/start/*/*/*/stop'));
        $matcher = new PathMatcher($regex);

        self::assertSame([
            'a',
            'b',
            'c',
        ], $matcher->extractWildcards('/start/a/b/c/stop'));
    }
}
