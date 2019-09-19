<?php

namespace Empress;

use Amp\Failure;
use Amp\Http\Server\Response;
use Amp\Success;
use Amp\Promise;
use function Amp\call;

class JsonTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform(\Amp\Promise $promise): Promise
    {
        return call(function () use ($promise) {

            /** @var \Amp\Http\Server\Response $response */
            $result = json_encode(yield $promise, \JSON_THROW_ON_ERROR);

            $response = new Response;
            $response->setHeader('content-type', 'application/json');
            $response->setBody($result);

            return new Success($response);
        });
    }
}
