<?php

declare(strict_types=1);

namespace Empress\Test\Routing;

use Closure;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerTypeEnum;
use Empress\Routing\Path\Path;
use Empress\Routing\Routes;
use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
    private Closure $closure;

    private Routes $routes;

    protected function setUp(): void
    {
        $this->closure = fn () => null;
        $this->routes = new Routes(new HandlerCollection());
    }

    public function testBefore(): void
    {
        $this->routes->before($this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::BEFORE, $entry?->getType());
        self::assertEquals(new Path('/*'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testBeforeAt(): void
    {
        $this->routes->beforeAt('/home', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::BEFORE, $entry?->getType());
        self::assertEquals(new Path('/home'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testAfter(): void
    {
        $this->routes->after($this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::AFTER, $entry?->getType());
        self::assertEquals(new Path('/*'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testAfterAt(): void
    {
        $this->routes->afterAt('/home', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::AFTER, $entry?->getType());
        self::assertEquals(new Path('/home'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testGroup(): void
    {
        $this->routes->group('/foo', function (Routes $routes): void {
            $routes->get('/bar', $this->closure);
        });

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::GET, $entry?->getType());
        self::assertEquals(new Path('/foo/bar'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testGroupWhereNestedHandlerHasNoSlash(): void
    {
        $this->routes->group('/foo', function (Routes $routes): void {
            $routes->get('bar', $this->closure);
        });

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::GET, $entry?->getType());
        self::assertEquals(new Path('/foo/bar'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testGet(): void
    {
        $this->routes->get('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::GET, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testPost(): void
    {
        $this->routes->post('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::POST, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testPut(): void
    {
        $this->routes->put('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::PUT, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testDelete(): void
    {
        $this->routes->delete('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::DELETE, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testPatch(): void
    {
        $this->routes->patch('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::PATCH, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testHead(): void
    {
        $this->routes->head('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::HEAD, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    public function testOptions(): void
    {
        $this->routes->options('/', $this->closure);

        $entry = $this->getEntry();

        self::assertSame(HandlerTypeEnum::OPTIONS, $entry?->getType());
        self::assertEquals(new Path('/'), $entry?->getPath());
        self::assertSame($this->closure, $entry?->getHandler());
    }

    private function getEntry(): ?HandlerEntry
    {
        return $this->routes->getHandlerCollection()->first();
    }
}
