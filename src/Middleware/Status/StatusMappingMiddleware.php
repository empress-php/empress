<?php

namespace Empress\Middleware\Status;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Internal\HaltAwareTrait;
use Empress\Internal\TypeAssertionTrait;
use Empress\Middleware\AggregateMiddlewareInterface;
use function Amp\call;

class StatusMappingMiddleware implements AggregateMiddlewareInterface
{
    use HaltAwareTrait, TypeAssertionTrait;

    /**
     * @var array<StatusHandler>
     */
    private $handlers = [];

    /**
     * @param StatusHandler $handler
     */
    public function addHandler($handler): void
    {
        $this->assertInstanceOf(StatusHandler::class, $handler);

        $this->handlers[] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {

            /** @var Response $response */
            $response = yield $requestHandler->handleRequest($request);
            $statusCode = $response->getStatus();
            $candidates = [];

            /** @var StatusHandler $handler */
            foreach ($this->handlers as $handler) {
                if ($handler->getStatus() === $statusCode) {
                    $response = new Response($statusCode);

                    if ($handler->hasHeaders()) {
                        if ($handler->satisfiesHeaders($request)) {
                            $injector = new ContextInjector($handler->getCallable(), $request, $response);

                            return yield $this->resolveInjectionResult($injector);
                        }
                    } else {
                        $candidates[$statusCode] = $handler;
                    }
                }
            }

            if (isset($candidates[$statusCode])) {

                /** @var StatusHandler $candidate */
                $candidate = $candidates[$statusCode];
                $injector = new ContextInjector($candidate->getCallable(), $request, new Response($statusCode));

                return yield $this->resolveInjectionResult($injector);
            }

            return $response;
        });
    }
}
