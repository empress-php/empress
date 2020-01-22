<?php

namespace Empress\Transformer;

use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use TypeError;
use function Amp\call;

class HtmlTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform(Promise $promise): Promise
    {
        return call(function () use ($promise) {
            $value = yield $promise;

            if (!\is_string($value)) {
                throw new TypeError('Expected string');
            }

            $response = new Response();
            $response->setHeader('content-type', 'text/html');
            $response->setBody($value);

            return new Success($response);
        });
    }
}
