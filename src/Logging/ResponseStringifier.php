<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\Payload;
use Amp\Promise;
use function Amp\call;

final class ResponseStringifier extends BaseRequestResponseStringifier
{
    public function __construct(
        private int $status,
        private array $headers,
        private InputStream $responseBody
    ) {
    }

    public function stringify(): Promise
    {
        return call(function () {
            $buffer = '';

            // General response data
            $buffer .= \sprintf(
                "\nResponse: [%d]\n",
                $this->status
            );

            $buffer .= $this->stringifyHeaders($this->headers);

            $bodyPayload = new Payload($this->responseBody);
            $buffer .= yield $this->stringifyBody($bodyPayload);

            return $buffer;
        });
    }
}
