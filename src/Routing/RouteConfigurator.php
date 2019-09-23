<?php

namespace Empress\Routing;

use Amp\Http\Status;
use Empress\ResponseTransformerInterface;

use function Amp\Http\Server\redirectTo;

/**
 * Configures application routes.
 */
class RouteConfigurator
{
    /** @var array */
    private $routes = [];

    /** @var string|null */
    private $currentPrefix;

    /** @var \Empress\ResponseTransformerInterface */
    private $currentResponseTransformer;


    /**
     * @param string $prefix
     * @param \Empress\ResponseTransformerInterface|null $responseTransformer
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
     * Creates a handler returning a Response with a specific redirect header.
     *
     * @param string $targetUri
     * @param int $statusCode
     * @return \Closure
     */
    public function redirectTo(string $targetUri, int $statusCode = Status::FOUND): \Closure
    {
        return function () use ($targetUri, $statusCode) {
            return redirectTo($targetUri, $statusCode);
        };
    }

    /**
     * Routes can be prefixed with one or more prefixes.
     * This allows for grouping routes that logically belong together.
     *
     * @param string $prefix
     * @param \Closure $closure
     * @param \Empress\ResponseTransformerInterface|null $responseTransformer
     * @return void
     */
    public function prefix(string $prefix, \Closure $closure, ?ResponseTransformerInterface $responseTransformer = null): void
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
