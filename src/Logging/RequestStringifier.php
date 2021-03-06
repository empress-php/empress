<?php

namespace Empress\Logging;

use Amp\Http\Server\RequestBody;
use Amp\Promise;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use function Amp\call;

class RequestStringifier extends BaseRequestResponseStringifier
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
                        HandlerType::toString($handler->getType()),
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
