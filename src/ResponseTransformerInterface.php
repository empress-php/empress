<?php

namespace Empress;

use Amp\Http\Server\Response;
use Amp\Promise;

interface ResponseTransformerInterface
{

    /**
     * Takes a value and transforms it into a promise that will
     * eventually resolve to a response.
     *
     * @param \Amp\Promise<\Amp\Http\Server\Response> $promise
     * @return \Amp\Promise<\Amp\Http\Server\Response>
     */
    public function transform(Promise $promise): Promise;
}
