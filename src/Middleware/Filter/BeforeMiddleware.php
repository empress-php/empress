<?php

namespace Empress\Middleware\Filter;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Empress\Exception\HaltException;
use Empress\Internal\ContextInjector;
use Empress\Internal\HaltAwareTrait;
use Empress\Internal\TypeAssertionTrait;
use Empress\Middleware\AggregateMiddlewareInterface;
use function Amp\call;

class BeforeMiddleware implements AggregateMiddlewareInterface
{
    use HaltAwareTrait, TypeAssertionTrait;

    /**
     * @var array<FilterHandler>
     */
    private $handlers = [];

    public function addHandler($handler): void
    {
        $this->assertInstanceOf(FilterHandler::class, $handler);

        $this->handlers[] = $handler;
    }

    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {

            /** @var FilterHandler $handler */
            foreach ($this->handlers as $handler) {
                $injector = new ContextInjector($handler->getCallable(), $request);

                try {
                    yield $injector->inject();
                } catch (HaltException $e) {
                    return $e->toResponse();
                }
            }

            return yield $requestHandler->handleRequest($request);
        });
    }
}
