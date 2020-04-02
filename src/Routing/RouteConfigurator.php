<?php

namespace Empress\Routing;

use Amp\Http\Server\Router as HttpRouter;
use Closure;
use Empress\Exception\RouteException;
use Empress\Internal\RequestHandler;
use Empress\Middleware\AfterMiddleware;
use Empress\Middleware\BeforeMiddleware;
use Psr\Container\ContainerInterface;

class RouteConfigurator
{

    /** @var HttpRouter */
    private $router;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * RouteConfigurator constructor.
     *
     */
    public function __construct()
    {
        $this->router = new HttpRouter();
        $this->container = null;
    }

    /**
     * Registers a before filter.
     *
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
     * Registers an after filter.
     *
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
     * Groups routes under one prefix.
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
     * Gets the underlying router instance.
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
     * Adds a GET route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function get(string $route, $handler): self
    {
        $this->addRoute('GET', $route, $handler);

        return $this;
    }

    /**
     * Adds a POST route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function post(string $route, $handler): self
    {
        $this->addRoute('POST', $route, $handler);

        return $this;
    }

    /**
     * Adds a PUT route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function put(string $route, $handler): self
    {
        $this->addRoute('PUT', $route, $handler);

        return $this;
    }

    /**
     * Adds a DELETE route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function delete(string $route, $handler): self
    {
        $this->addRoute('DELETE', $route, $handler);

        return $this;
    }

    /**
     * Adds a PATCH route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function patch(string $route, $handler): self
    {
        $this->addRoute('PATCH', $route, $handler);

        return $this;
    }

    /**
     * Adds a HEAD route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function head(string $route, $handler): self
    {
        $this->addRoute('HEAD', $route, $handler);

        return $this;
    }

    /**
     * Adds an OPTIONS route.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteConfigurator
     * @throws RouteException
     */
    public function options(string $route, $handler): self
    {
        $this->addRoute('OPTIONS', $route, $handler);

        return $this;
    }

    /**
     * @param ContainerInterface $container
     */
    public function useContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $method
     * @param string $route
     * @param $handler
     * @return $this
     * @throws RouteException
     */
    private function addRoute(string $method, string $route, $handler): self
    {
        $this->router->addRoute($method, $route, new RequestHandler($this->parseHandler($handler)));

        return $this;
    }

    /**
     * @param string $handler
     * @return callable
     * @throws RouteException
     */
    private function parseHandler($handler): callable
    {
        if (\is_callable($handler)) {
            return $handler;
        }

        if (\is_string($handler) && \strpos($handler, '@') === false) {
            throw new RouteException('Provided handler is not callable.');
        }

        [$class, $method] = \explode('@', $handler);

        if ($this->container->has($class)) {
            $object = $this->container->get($class);

            return [$object, $method];
        }
        throw new RouteException(\sprintf('Class: %s not found in container', $class));
    }
}
