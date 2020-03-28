<?php

namespace Empress\Routing;

use Amp\Http\Server\Router as HttpRouter;
use Empress\Internal\RequestHandler;
use Closure;
use Empress\Middleware\AfterMiddleware;
use Empress\Middleware\BeforeMiddleware;

class RouteConfigurator
{

    /** @var HttpRouter */
    private $router;

    /**
     * @var array
     */
    private $filters;

    /**
     * RouteConfigurator constructor.
     */
    public function __construct()
    {
        $this->router = new HttpRouter();
    }

    /**
     * Registers a before filter
     * @param callable $callable
     * @return $this
     */
    public function before(callable $callable): self
    {
        $beforeMiddleware = new BeforeMiddleware($callable);

        $this->filters[] = $beforeMiddleware;

        return $this;
    }

    /**
     * Registers an after filter
     * @param callable $callable
     * @return $this
     */
    public function after(callable $callable): self
    {
        $afterMiddleware = new AfterMiddleware($callable);

        $this->filters[] = $afterMiddleware;

        return $this;
    }

    /**
     * Groups routes under one prefix
     *
     * @param string $prefix
     * @param Closure $closure
     * @return RouteConfigurator
     */
    public function group(string $prefix, Closure $closure): self
    {
        $router = new self;
        $router->getRouter()->prefix($prefix);
        $closure($router);
        $this->router->merge($router->getRouter());

        return $this;
    }

    /**
     * Gets the underlying router instance
     *
     * @return HttpRouter
     */
    public function getRouter(): HttpRouter
    {

        if (!empty($this->filters)) {
            $this->router->stack(...$this->filters);
        }

        return $this->router;
    }

    /**
     * Adds a GET route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function get(string $route, callable $handler): self
    {
        $this->router->addRoute('GET', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds a POST route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function post(string $route, callable $handler): self
    {
        $this->router->addRoute('POST', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds a PUT route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function put(string $route, callable $handler): self
    {
        $this->router->addRoute('PUT', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds a DELETE route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function delete(string $route, callable $handler): self
    {
        $this->router->addRoute('DELETE', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds a PATCH route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function patch(string $route, callable $handler): self
    {
        $this->router->addRoute('PATCH', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds a HEAD route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function head(string $route, callable $handler): self
    {
        $this->router->addRoute('HEAD', $route, new RequestHandler($handler));

        return $this;
    }

    /**
     * Adds an OPTIONS route
     *
     * @param string $route
     * @param callable $handler
     * @return RouteConfigurator
     */
    public function options(string $route, callable $handler): self
    {
        $this->router->addRoute('OPTIONS', $route, new RequestHandler($handler));

        return $this;
    }
}