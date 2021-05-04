<?php

namespace Empress\Test\Routing;

use Closure;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;
use Empress\Routing\Routes;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
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

        static::assertEquals(HandlerType::BEFORE, $entry?->getType());
        static::assertEquals(new Path('/*'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testBeforeAt(): void
    {
        $this->routes->beforeAt('/home', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::BEFORE, $entry?->getType());
        static::assertEquals(new Path('/home'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testAfter(): void
    {
        $this->routes->after($this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::AFTER, $entry?->getType());
        static::assertEquals(new Path('/*'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testAfterAt(): void
    {
        $this->routes->afterAt('/home', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::AFTER, $entry?->getType());
        static::assertEquals(new Path('/home'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testGroup(): void
    {
        $this->routes->group('/foo', function (Routes $routes) {
            $routes->get('/bar', $this->closure);
        });

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::GET, $entry?->getType());
        static::assertEquals(new Path('/foo/bar'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testGroupWhereNestedHandlerHasNoSlash(): void
    {
        $this->routes->group('/foo', function (Routes $routes) {
            $routes->get('bar', $this->closure);
        });

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::GET, $entry?->getType());
        static::assertEquals(new Path('/foo/bar'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testGet(): void
    {
        $this->routes->get('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::GET, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testPost(): void
    {
        $this->routes->post('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::POST, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testPut(): void
    {
        $this->routes->put('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::PUT, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testDelete(): void
    {
        $this->routes->delete('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::DELETE, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testPatch(): void
    {
        $this->routes->patch('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::PATCH, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testHead(): void
    {
        $this->routes->head('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::HEAD, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    public function testOptions(): void
    {
        $this->routes->options('/', $this->closure);

        $entry = $this->getEntry();

        static::assertEquals(HandlerType::OPTIONS, $entry?->getType());
        static::assertEquals(new Path('/'), $entry?->getPath());
        static::assertEquals($this->closure, $entry?->getHandler());
    }

    private function getEntry(): ?HandlerEntry
    {
        return $this->routes->getHandlerCollection()->first();
    }
}
