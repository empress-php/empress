<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

class RouteConfigurator
{
    /** @var array */
    private $routes = [];

    /** @var string|null */
    private $currentPrefix;

    /** @var \Empress\ResponseTransformerInterface */
    private $currentResponseTransformer;


    public function __construct(string $prefix = '', ?ResponseTransformerInterface $responseTransformer = null)
    {
        $this->currentPrefix = $prefix;
        $this->currentResponseTransformer = $responseTransformer;
    }

    public function get(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('GET', $uri, $handler, $responseTransformer);
    }

    public function post(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('POST', $uri, $handler, $responseTransformer);
    }

    public function put(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('PUT', $uri, $handler, $responseTransformer);
    }

    public function patch(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('PATCH', $uri, $handler, $responseTransformer);
    }

    public function head(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('HEAD', $uri, $handler, $responseTransformer);
    }

    public function options(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('OPTIONS', $uri, $handler, $responseTransformer);
    }

    public function delete(string $uri, $handler, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $this->route('DELETE', $uri, $handler, $responseTransformer);
    }

    public function prefix(string $prefix, \Closure $closure, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $newSelf = new self($this->currentPrefix . $prefix, $responseTransformer ?? $this->currentResponseTransformer);
        $closure($newSelf);
        $this->routes = array_merge($this->routes, $newSelf->getRoutes());
    }

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
