<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Internal\HaltAwareTrait;
use function Amp\call;

class AfterMiddleware implements Middleware
{
    use HaltAwareTrait;

    /**
     * @var callable
     */
    private $handler;

    /**
     * AfterMiddleware constructor.
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            $response = yield $requestHandler->handleRequest($request);
            $injector = new ContextInjector($this->handler, $request, $response);

            return yield $this->resolveInjectionResult($injector);
        });
    }
}
