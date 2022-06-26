<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\Http\Server\RequestBody;
use Amp\Promise;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use function Amp\call;

final class RequestStringifier extends BaseRequestResponseStringifier
{
    public function __construct(
        private string $method,
        private string $path,
        private array $headers,
        private RequestBody $requestBody,
        private ?HandlerCollection $handlerCollection = null
    ) {
    }

    /**
     * @return Promise<string>
     */
    public function stringify(): Promise
    {
        return call(function () {
            $buffer = '';

            // General request data
            $buffer .= \sprintf(
                "\nRequest: %s [%s]\n",
                $this->method,
                $this->path
            );

            // Matched handlers
            if ($this->handlerCollection !== null) {
                $buffer .= "Matched handlers:\n";

                /** @var HandlerEntry $handler */
                foreach ($this->handlerCollection as $handler) {
                    $buffer .= \sprintf(
                        "\t%s [%s]\n",
                        $handler->getType()->value,
                        (string) $handler->getPath()
                    );
                }
            }

            $buffer .= $this->stringifyHeaders($this->headers);
            $buffer .= yield $this->stringifyBody($this->requestBody);

            return $buffer;
        });
    }
}
