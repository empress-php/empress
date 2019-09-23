<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

/**
 * Enables definitions to be embellished with response transformers.
 */
interface TransformableDefinitionInterface
{

    /**
     * Gets the response tranformer associated with this instance.
     *
     * @return \Empress\Routing\ResponseTransformerInterface|null
     */
    public function getResponseTransformer(): ?ResponseTransformerInterface;

    /**
     * Registers a response transformer on this instance.
     *
     * @param \Empress\ResponseTransformerInterface $responseTransformer
     * @return void
     */
    public function setResponseTransformer(?ResponseTransformerInterface $responseTransformer): void;
}
