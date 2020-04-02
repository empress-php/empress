<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Internal\HaltAwareTrait;
use function Amp\call;

class BeforeMiddleware implements Middleware
{
    use HaltAwareTrait;

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

            return yield $this->resolveInjectionResult($injector, function () use ($request, $requestHandler) {
                return $requestHandler->handleRequest($request);
            });
        });
    }
}
