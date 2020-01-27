<?php

namespace Empress\Routing;

use Closure;
use Empress\Transformer\ResponseTransformerInterface;

/**
 * Configures application routes.
 */
class RouteConfigurator
{
    /** @var array */
    private $routes = [];

    /** @var string|null */
    private $currentPrefix;

    /** @var ResponseTransformerInterface */
    private $currentResponseTransformer;

    /**
     * @param string $prefix
     * @param ResponseTransformerInterface|null $responseTransformer
     */
    public function __construct(string $prefix = '', ?ResponseTransformerInterface $responseTransformer = null)
    {
        $this->currentPrefix = $prefix;
        $this->currentResponseTransformer = $responseTransformer;
    }

    /**
     * Defines a GET route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function get(...$args): self
    {
        $this->route('GET', ...$args);

        return $this;
    }

    /**
     * Defines a POST route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function post(...$args): self
    {
        $this->route('POST', ...$args);

        return $this;
    }

    /**
     * Defines a PUT route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function put(...$args): self
    {
        $this->route('PUT', ...$args);

        return $this;
    }

    /**
     * Defines a PATCH route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function patch(...$args): self
    {
        $this->route('PATCH', ...$args);

        return $this;
    }

    /**
     * Defines a HEAD route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function head(...$args): self
    {
        $this->route('HEAD', ...$args);

        return $this;
    }

    /**
     * Defines an OPTION route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function options(...$args): self
    {
        $this->route('OPTIONS', ...$args);

        return $this;
    }

    /**
     * Defines a DELETE route.
     *
     * @param mixed ...$args
     * @return self
     */
    public function delete(...$args): self
    {
        $this->route('DELETE', ...$args);

        return $this;
    }

    /**
     * Mounts a controller.
     *
     * @param object $controller
     * @return $this
     * @throws \Empress\Exception\RouteException
     */
    public function mount(object $controller): self
    {
        $mounter = new ControllerMounter($controller);

        $this->routes = \array_merge($this->routes, $mounter->getRoutes());

        return $this;
    }

    /**
     * Routes can be prefixed with one or more prefixes.
     * This allows for grouping routes that logically belong together.
     *
     * @param string $prefix
     * @param Closure $closure
     * @param ResponseTransformerInterface|null $responseTransformer
     * @return void
     */
    public function prefix(string $prefix, Closure $closure, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $newSelf = new self($this->currentPrefix . $prefix, $responseTransformer ?? $this->currentResponseTransformer);
        $closure($newSelf);
        $this->routes = \array_merge($this->routes, $newSelf->getRoutes());
    }

    /**
     * Gets all defined routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function route(string $verb, string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null)
    {
        $definition = new RouteDefinition($verb, $uri, $handler);
        $definition->setResponseTransformer($this->currentResponseTransformer ?? $responseTransformer);

        $this->routes[$this->currentPrefix][] = $definition;
    }
}
