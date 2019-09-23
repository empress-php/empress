<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

/**
 * Defines a single route.
 */
class RouteDefinition implements TransformableDefinitionInterface
{

    /** @var string */
    private $verb;

    /** @var string */
    private $uri;

    /** @var mixed */
    private $handler;

    /** @var \Empress\ResponseTransformerInterface */
    private $reponseTransformer;

    /**
     * @param string $verb
     * @param string $uri
     * @param mixed $handler
     */
    public function __construct(string $verb, string $uri, $handler)
    {
        $this->verb = \strtoupper($verb);
        $this->uri = $uri;
        $this->handler = $handler;
    }

    /**
     * Get the HTTP verb for the route.
     *
     * @return string
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * Gets the URI for the route.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Gets the request handler associated with this route.
     *
     * @return void
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /** @inheritDoc */
    public function getResponseTransformer(): ?ResponseTransformerInterface
    {
        return $this->reponseTransformer;
    }

    /** @inheritDoc */
    public function setResponseTransformer(?ResponseTransformerInterface $reponseTransformer): void
    {
        $this->reponseTransformer = $reponseTransformer;
    }
}
