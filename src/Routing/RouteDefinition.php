<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

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

    public function __construct(string $verb, string $uri, $handler)
    {
        $this->verb = $verb;
        $this->uri = $uri;
        $this->handler = $handler;
    }

    public function getVerb(): string
    {
        return $this->verb;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    /** @inheritDoc */
    public function getResponseTransformers(): ResponseTransformerInterface
    {
        return $this->reponseTransformer;
    }

    /** @inheritDoc */
    public function addResponseTransformer(ResponseTransformerInterface $reponseTransformer): void
    {
        $this->reponseTransformer = $reponseTransformer;
    }
}
