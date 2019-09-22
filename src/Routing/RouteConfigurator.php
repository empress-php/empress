<?php

namespace Empress\Routing;

use Amp\Http\Status;
use Empress\ResponseTransformerInterface;

use function Amp\Http\Server\redirectTo;

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

    public function get(...$args): self
    {
        $this->route('GET', ...$args);

        return $this;
    }

    public function post(...$args): self
    {
        $this->route('POST', ...$args);

        return $this;
    }

    public function put(...$args): self
    {
        $this->route('PUT', ...$args);

        return $this;
    }

    public function patch(...$args): self
    {
        $this->route('PATCH', ...$args);

        return $this;
    }

    public function head(...$args): self
    {
        $this->route('HEAD', ...$args);

        return $this;
    }

    public function options(...$args): self
    {
        $this->route('OPTIONS', ...$args);

        return $this;
    }

    public function delete(...$args): self
    {
        $this->route('DELETE', ...$args);

        return $this;
    }

    public function redirectTo(string $targetUri, int $statusCode = Status::FOUND): \Closure
    {
        return function () use ($targetUri, $statusCode) {
            return redirectTo($targetUri, $statusCode);
        };
    }

    public function prefix(string $prefix, \Closure $closure, ?ResponseTransformerInterface $responseTransformer = null): void
    {
        $newSelf = new self($this->currentPrefix . $prefix, $responseTransformer ?? $this->currentResponseTransformer);
        $closure($newSelf);
        $this->routes = \array_merge($this->routes, $newSelf->getRoutes());
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
