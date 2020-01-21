<?php

namespace Empress\Internal;

use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Empress\ResponseTransformerInterface;

use function Amp\call;

class PlainTextTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform(Promise $promise): Promise
    {
        return call(function () use ($promise) {
            $value = yield $promise;

            if (!\is_string($value)) {
                throw new \TypeError('Expected string');
            }

            $response = new Response();
            $response->setHeader('content-type', 'text/plain');
            $response->setBody($value);

            return new Success($response);
        });
    }
}
