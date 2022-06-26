<?php

declare(strict_types=1);

namespace Empress\Routing;

use Closure;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerTypeEnum;
use Empress\Routing\Path\Path;

final class Routes
{
    private string $prefix = '';

    public function __construct(private HandlerCollection $handlerCollection)
    {
    }

    /**
     * Registers a before filter.
     */
    public function before(callable $callable): self
    {
        $this->addEntry(HandlerTypeEnum::BEFORE, '/*', $callable);

        return $this;
    }

    /**
     * Registers a before filter.
     */
    public function beforeAt(string $path, callable $callable): self
    {
        $this->addEntry(HandlerTypeEnum::BEFORE, $path, $callable);

        return $this;
    }

    /**
     * Registers an after filter.
     */
    public function after(callable $callable): self
    {
        $this->addEntry(HandlerTypeEnum::AFTER, '/*', $callable);

        return $this;
    }

    /**
     * Registers an after filter.
     */
    public function afterAt(string $path, callable $callable): self
    {
        $this->addEntry(HandlerTypeEnum::AFTER, $path, $callable);

        return $this;
    }

    /**
     * Groups routes under one prefix.
     *
     * @psalm-param \Closure(Routes): void $closure
     */
    public function group(string $prefix, Closure $closure): self
    {
        $routes = new self(new HandlerCollection());
        $routes->prefix = $prefix;

        $closure($routes);

        $this->handlerCollection = $this->handlerCollection->merge($routes->handlerCollection);

        return $routes;
    }

    /**
     * Adds a GET route.
     */
    public function get(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::GET, $route, $handler);

        return $this;
    }

    /**
     * Adds a POST route.
     */
    public function post(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::POST, $route, $handler);

        return $this;
    }

    /**
     * Adds a PUT route.
     */
    public function put(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::PUT, $route, $handler);

        return $this;
    }

    /**
     * Adds a DELETE route.
     */
    public function delete(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::DELETE, $route, $handler);

        return $this;
    }

    /**
     * Adds a PATCH route.
     */
    public function patch(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::PATCH, $route, $handler);

        return $this;
    }

    /**
     * Adds a HEAD route.
     */
    public function head(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::HEAD, $route, $handler);

        return $this;
    }

    /**
     * Adds an OPTIONS route.
     */
    public function options(string $route, callable $handler): self
    {
        $this->addEntry(HandlerTypeEnum::OPTIONS, $route, $handler);

        return $this;
    }

    public function getHandlerCollection(): HandlerCollection
    {
        return $this->handlerCollection;
    }

    public function addEntry(HandlerTypeEnum $handlerType, string $route, callable $handler): void
    {
        $entry = new HandlerEntry($handlerType, new Path($this->prefixRoute($route)), $handler);

        $this->handlerCollection->add($entry);
    }

    private function prefixRoute(string $route): string
    {
        return \rtrim($this->prefix, '/') . '/' . \ltrim($route, '/');
    }
}
