<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;
use Empress\Internal\CaseConverter;

class ControllerDefinition implements TransformableDefinitionInterface
{

    /** @var string */
    private $routerPrefix;

    /** @var \Empress\Routing\RouteDefinition[] */
    private $routeDefinitions;

    /** @var \Empress\ResponseTransformerInterface */
    private $responseTransformer;

    public function __construct(string $class = '', RouteDefinition ...$routeDefinitions)
    {
        $classBasename = array_slice(explode('\\', $class), -1)[0];

        if (!preg_match('/Controller$/', $classBasename)) {
            throw new \InvalidArgumentException(sprintf('Wrong class name: "%s"', $classBasename));
        }

        $bareName = preg_split('/Controller$/', $classBasename)[0];
        $converter = new CaseConverter($bareName);

        $this->routerPrefix = $class ? $converter->kebabCasify() : '';
        $this->routeDefinitions = $routeDefinitions;
    }

    public function getRouterPrefix(): string
    {
        return $this->routerPrefix;
    }

    public function getRouteDefinitions(): array
    {
        return $this->routeDefinitions;
    }

    public function getResponseTransformer(): ResponseTransformerInterface
    {
        return $this->responseTransformer;
    }

    public function setResponseTransformer(ResponseTransformerInterface $responseTransformer): void
    {
        $this->responseTransformer = $responseTransformer;
    }
}
