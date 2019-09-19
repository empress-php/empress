<?php

namespace Empress\Routing;

use Empress\ResponseTransformerInterface;

/**
 * Enable definitions to be embellished with response transformers
 */
interface TransformableDefinitionInterface
{

    /**
     * Get a response tranformer associated with this instance
     *
     * @return \Empress\Routing\ResponseTransformerInterface
     */
    public function getResponseTransformer(): ResponseTransformerInterface;

    /**
     * Register a response transformer on this instance
     *
     * @param \Empress\ResponseTransformerInterface $responseTransformer
     * @return void
     */
    public function setResponseTransformer(ResponseTransformerInterface $responseTransformer): void;
}
