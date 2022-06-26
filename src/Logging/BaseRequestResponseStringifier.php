<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\ByteStream\Payload;
use Amp\Promise;
use function Amp\call;

abstract class BaseRequestResponseStringifier implements StringifierInterface
{
    protected function stringifyHeaders(array $headers): string
    {
        $buffer = "Headers:\n";

        foreach ($headers as $headerName => $headerValues) {
            $buffer .= \sprintf(
                "\t%s: %s\n",
                $headerName,
                \implode(',', $headerValues)
            );
        }

        return $buffer;
    }

    protected function stringifyBody(Payload $body): Promise
    {
        return call(function () use ($body) {
            $responseBody = yield $body->buffer();

            return \sprintf(
                "\tBody: %s\n",
                \mb_strimwidth($responseBody, 0, static::MAX_BODY_LENGTH, '...')
            );
        });
    }
}
