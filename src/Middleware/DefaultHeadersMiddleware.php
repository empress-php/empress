<?php

declare(strict_types=1);

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Promise;

use function Amp\call;

/**
 * Middleware that adds additional response headers to each response.
 */
final class DefaultHeadersMiddleware implements Middleware
{
    /**
     * DefaultHeadersMiddleware constructor.
     *
     * @param array $headers Default headers to be used with every response
     */
    public function __construct(private array $headers)
    {
    }

    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {

            /** @var Response $response */
            $response = yield $requestHandler->handleRequest($request);
            $response->setHeaders($this->headers);

            return $response;
        });
    }
}
