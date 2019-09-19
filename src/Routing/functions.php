<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

function route(...$args): RouteDefinition
{
    return new RouteDefinition(...$args);
}

function controller(string $class, RouteDefinition ...$args): ControllerDefinition
{
    return new ControllerDefinition($class, ...$args);
}

function transform(TransformableDefinitionInterface $definition, ResponseTransformerInterface $responseTransformer)
{
    $definition->addResponseTransformer($responseTransformer);

    return $definition;
}
