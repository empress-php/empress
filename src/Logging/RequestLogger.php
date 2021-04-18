<?php

namespace Empress\Logging;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Routing\Handler\HandlerCollection;
use Psr\Log\LoggerInterface;
use function Amp\call;

class RequestLogger
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function debug(Request $request, Response $response, ?HandlerCollection $handlerCollection = null): Promise
    {
        return call(function () use ($request, $response, $handlerCollection) {
            $requestStringifier = new RequestStringifier(
                $request->getMethod(),
                $request->getUri()->getPath(),
                $request->getHeaders(),
                $request->getBody(),
                $handlerCollection
            );

            $responseStringifier = new ResponseStringifier(
                $response->getStatus(),
                $response->getHeaders(),
                clone $response->getBody() // Don't consume the body that's supposed to be sent to the client
            );

            $this->logger->debug(yield $requestStringifier->stringify());
            $this->logger->debug(yield $responseStringifier->stringify());
        });
    }
}
