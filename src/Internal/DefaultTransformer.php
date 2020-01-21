<?php

namespace Empress\Internal;

use Amp\Promise;
use Amp\Success;
use Empress\ResponseTransformerInterface;
use InvalidArgumentException;

use function Amp\call;

class DefaultTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform($promise): Promise
    {
        return call(function () use ($promise) {
            $value = yield $promise;

            if (\is_string($value)) {
                $transformer = new PlainTextTransformer;
            } elseif (\is_array($value)) {
                $transformer = new JsonTransformer;
            } else {
                throw new InvalidArgumentException('Expected a string or array');
            }

            return $transformer->transform(new Success($value));
        });
    }
}
