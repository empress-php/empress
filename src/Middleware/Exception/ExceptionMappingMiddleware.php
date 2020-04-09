<?php

namespace Empress\Middleware\Exception;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Internal\TypeAssertionTrait;
use Empress\Middleware\AggregateMiddlewareInterface;
use Throwable;
use function Amp\call;

class ExceptionMappingMiddleware implements AggregateMiddlewareInterface
{
    use TypeAssertionTrait;

    /**
     * @var array<ExceptionHandler>
     */
    private $handlers = [];

    /**
     * @param ExceptionHandler $handler
     */
    public function addHandler($handler): void
    {
        $this->assertInstanceOf(ExceptionHandler::class, $handler);

        $this->handlers[] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            $response = null;

            try {
                $response = yield $requestHandler->handleRequest($request);
            } catch (Throwable $exception) {

                /** @var ExceptionHandler $handler */
                foreach ($this->handlers as $handler) {
                    if ($handler->getExceptionClass() === get_class($exception)) {
                        $injector = new ContextInjector($handler->getCallable(), $request, $response, $exception);

                        return yield $injector->inject();
                    }
                }

                throw $exception;
            }

            return $response;
        });
    }
}