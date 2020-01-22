<?php

namespace Empress\Transformer;

use Amp\Promise;

use function Amp\call;

class DefaultTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform($promise): Promise
    {
        return call(function () use ($promise) {
            $value = yield $promise;

            if (\is_string($value)) {
                $transformer = new HtmlTransformer;
            } elseif (\is_array($value)) {
                $transformer = new JsonTransformer;
            } else {
                return $promise;
            }

            return $transformer->transform($promise);
        });
    }
}
