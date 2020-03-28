<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Amp\Success;
use Empress\Internal\ContextInjector;
use Throwable;
use function Amp\call;

class BeforeMiddleware implements Middleware
{

    /**
     * @var callable
     */
    private $handler;

    /**
     * BeforeMiddleware constructor.
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            $injector = new ContextInjector($this->handler, $request);
            yield $injector->inject();

            return $requestHandler->handleRequest($request);
        });
    }
}