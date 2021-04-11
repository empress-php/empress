<?php

namespace Empress\Routing;

use Closure;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;

class Routes
{
    private string $prefix = '';

    public function __construct(private HandlerCollection $handlerCollection)
    {
    }

    /**
     * Registers a before filter.
     *
     * @param callable $callable
     * @return Routes
     */
    public function before(callable $callable): self
    {
        $this->addEntry(HandlerType::BEFORE, '/*', $callable);

        return $this;
    }

    /**
     * Registers a before filter.
     *
     * @param string $path
     * @param callable $callable
     * @return Routes
     */
    public function beforeAt(string $path, callable $callable): self
    {
        $this->addEntry(HandlerType::BEFORE, $path, $callable);

        return $this;
    }

    /**
     * Registers an after filter.
     *
     * @param callable $callable
     * @return Routes
     */
    public function after(callable $callable): self
    {
        $this->addEntry(HandlerType::AFTER, '/*', $callable);

        return $this;
    }

    /**
     * Registers an after filter.
     *
     * @param string $path
     * @param callable $callable
     * @return Routes
     */
    public function afterAt(string $path, callable $callable): self
    {
        $this->addEntry(HandlerType::AFTER, $path, $callable);

        return $this;
    }

    /**
     * Groups routes under one prefix.
     *
     * @param string $prefix
     * @param Closure $closure,
     * @return Routes
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
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function get(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::GET, $route, $handler);

        return $this;
    }

    /**
     * Adds a POST route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function post(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::POST, $route, $handler);

        return $this;
    }

    /**
     * Adds a PUT route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function put(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::PUT, $route, $handler);

        return $this;
    }

    /**
     * Adds a DELETE route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function delete(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::DELETE, $route, $handler);

        return $this;
    }

    /**
     * Adds a PATCH route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function patch(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::PATCH, $route, $handler);

        return $this;
    }

    /**
     * Adds a HEAD route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function head(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::HEAD, $route, $handler);

        return $this;
    }

    /**
     * Adds an OPTIONS route.
     *
     * @param string $route
     * @param callable $handler
     * @return Routes
     */
    public function options(string $route, callable $handler): self
    {
        $this->addEntry(HandlerType::OPTIONS, $route, $handler);

        return $this;
    }

    public function getHandlerCollection(): HandlerCollection
    {
        return $this->handlerCollection;
    }

    public function addEntry(int $handlerType, string $route, callable $handler): void
    {
        $entry = new HandlerEntry($handlerType, new Path($this->prefixRoute($route)), $handler);

        $this->handlerCollection->add($entry);
    }

    private function prefixRoute(string $route): string
    {
        return \rtrim($this->prefix, '/') . '/' . \ltrim($route, '/');
    }
}
