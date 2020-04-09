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

class AfterMiddleware implements AggregateMiddlewareInterface
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

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            $response = yield $requestHandler->handleRequest($request);

            /** @var FilterHandler $handler */
            foreach ($this->handlers as $handler) {
                $injector = new ContextInjector($handler->getCallable(), $request, $response);

                try {
                    $response = yield $injector->inject();
                } catch (HaltException $e) {
                    return $e->toResponse();
                }
            }

            return $response;
        });
    }
}
