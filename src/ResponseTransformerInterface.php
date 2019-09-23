<?php

namespace Empress;

use Amp\Promise;

interface ResponseTransformerInterface
{

    /**
     * Takes a value wrapped in a promise and transforms it into another promise that will
     * eventually resolve to a response.
     *
     * @param \Amp\Promise<\Amp\Http\Server\Response> $promise
     * @return \Amp\Promise<\Amp\Http\Server\Response>
     */
    public function transform(Promise $promise): Promise;
}
