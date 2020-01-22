<?php

namespace Empress\Transformer;

use Amp\Promise;

interface ResponseTransformerInterface
{

    /**
     * Takes a value wrapped in a promise and transforms it into another promise that will
     * eventually resolve to a response.
     *
     * @param Promise $promise
     * @return Promise
     */
    public function transform(Promise $promise): Promise;
}
