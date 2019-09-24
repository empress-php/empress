<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;

use function Amp\call;

/**
 * Middleware that adds additional response headers to each response.
 */
class DefaultHeadersMiddleware implements Middleware
{

    /** @var array */
    private $headers;

    /**
     * @param array $headers Default headers to be used with every response
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /** @inheritDoc */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {

            /** @var \Amp\Http\Server\Response $response */
            $response = yield $requestHandler->handleRequest($request);
            $response->setHeaders($this->headers);

            return $response;
        });
    }
}
