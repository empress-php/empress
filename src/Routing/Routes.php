<?php

namespace Empress\Routing;

use Closure;

class Routes
{

    /**
     * @var HandlerGroup
     */
    private $group;

    /**
     * Routes constructor.
     *
     * @param HandlerGroup $group
     */
    public function __construct(HandlerGroup $group)
    {
        $this->group = $group;
    }

    /**
     * Registers a before filter.
     *
     * @param callable $callable
     * @return $this
     */
    public function before(callable $callable): self
    {
        $this->group->addBeforeFilter($callable);

        return $this;
    }

    public function beforeAt(string $path, callable $callable)
    {
        $this->group->addBeforeFilter($callable, $path);
    }

    /**
     * Registers an after filter.
     *
     * @param callable $callable
     * @return $this
     */
    public function after(callable $callable): self
    {
        $this->group->addAfterFilter($callable);

        return $this;
    }

    public function afterAt(string $path, callable $callable)
    {
        $this->group->addAfterFilter($callable, $path);
    }

    /**
     * Groups routes under one prefix.
     *
     * @param string $prefix
     * @param Closure $closure
     * @return Routes
     */
    public function group(string $prefix, Closure $closure): self
    {
        $routes = new self(new HandlerGroup($prefix));
        $closure($routes);
        $this->group->merge($routes->group);

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
        $this->group->addRoute(HandlerType::GET, $route, $handler);

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
        $this->group->addRoute(HandlerType::POST, $route, $handler);

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
        $this->group->addRoute(HandlerType::PUT, $route, $handler);

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
        $this->group->addRoute(HandlerType::DELETE, $route, $handler);

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
        $this->group->addRoute(HandlerType::PATCH, $route, $handler);

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
        $this->group->addRoute(HandlerType::HEAD, $route, $handler);

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
        $this->group->addRoute(HandlerType::OPTIONS, $route, $handler);

        return $this;
    }
}
