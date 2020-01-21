<?php

namespace Empress\Internal;

use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Empress\ResponseTransformerInterface;
use function Amp\call;

class JsonTransformer implements ResponseTransformerInterface
{

    /** @inheritDoc */
    public function transform(\Amp\Promise $promise): Promise
    {
        return call(function () use ($promise) {
            $value = yield $promise;

            if (\PHP_VERSION >= 70300) {
                $result = \json_encode($value, \JSON_THROW_ON_ERROR);
            } else {
                $result = \json_encode($value);

                if (($lastError = \json_last_error()) !== \JSON_ERROR_NONE) {
                    throw new \JsonException(\json_last_error_msg(), $lastError);
                }
            }

            $response = new Response;
            $response->setHeader('content-type', 'application/json');
            $response->setBody($result);

            return new Success($response);
        });
    }
}
