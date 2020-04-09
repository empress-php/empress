<?php

namespace Empress\Routing;

use Amp\Http\Server\Router;
use Closure;
use Empress\Exception\RouteException;
use Empress\Internal\RequestHandler;
use Empress\Middleware\Exception\ExceptionHandler;
use Empress\Middleware\Exception\ExceptionMappingMiddleware;
use Empress\Middleware\Filter\AfterMiddleware;
use Empress\Middleware\Filter\BeforeMiddleware;
use Empress\Middleware\Filter\FilterHandler;
use Empress\Middleware\Status\StatusHandler;
use Empress\Middleware\Status\StatusMappingMiddleware;
use Psr\Container\ContainerInterface;

class Routes
{

    /** @var  Router */
    private $router;

    /**
     * @var BeforeMiddleware
     */
    private $beforeMiddleware;

    /**
     * @var BeforeMiddleware
     */
    private $afterMiddleware;

    /**
     * @var StatusMappingMiddleware
     */
    private $statusMappingMiddleware;

    /**
     * @var ExceptionMappingMiddleware
     */
    private $exceptionMappingMiddleware;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var StatusMappingMiddleware
     */
    private $serverErrorHandler;

    /**
     * Routes constructor.
     *
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->serverErrorHandler = new StatusMappingMiddleware();
        $this->container = null;

        $this->beforeMiddleware = new BeforeMiddleware();
        $this->afterMiddleware = new AfterMiddleware();
        $this->statusMappingMiddleware = new StatusMappingMiddleware();
        $this->exceptionMappingMiddleware = new ExceptionMappingMiddleware();
    }

    /**
     * Registers a before filter.
     *
     * @param callable $callable
     * @return $this
     */
    public function before(callable $callable): self
    {
        $beforeFilter = new FilterHandler($callable);

        $this->beforeMiddleware->addHandler($beforeFilter);

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
        $afterFilter = new FilterHandler($callable);

        $this->afterMiddleware->addHandler($afterFilter);

        return $this;
    }

    public function exception(string $exceptionClass, callable $callable): self
    {
        $exceptionHandler = new ExceptionHandler($exceptionClass, $callable);

        $this->exceptionMappingMiddleware->addHandler($exceptionHandler);

        return $this;
    }

    public function status(int $status, callable $callable, array $headers = []): self
    {
        $statusHandler = new StatusHandler($status, $callable, $headers);

        $this->statusMappingMiddleware->addHandler($statusHandler);

        return $this;
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
        $router = new self;
        $router->getRouter()->prefix($prefix);
        $closure($router);
        $this->router->merge($router->getRouter());

        return $this;
    }

    /**
     * Gets the underlying router instance.
     *
     * @return
     */
    public function getRouter(): Router
    {
        $this->router->stack(
            $this->exceptionMappingMiddleware,
            $this->beforeMiddleware,
            $this->afterMiddleware,
            $this->statusMappingMiddleware
        );

        return $this->router;
    }

    /**
     * Adds a GET route.
     *
     * @param string $route
     * @param mixed $handler
     * @return Routes
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
     * @return Routes
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
     * @return Routes
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
     * @return Routes
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
     * @return Routes
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
     * @return Routes
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
     * @return Routes
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
