<?php

namespace Empress\Routing\Exception;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use Throwable;
use function Amp\call;

class ExceptionMapper
{

    /**
     * @var array<ExceptionHandler>
     */
    private $handlers = [];

    /**
     * @param ExceptionHandler $handler
     */
    public function addHandler(ExceptionHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function process(Throwable $exception, Request $request, Response $response = null): Promise
    {
        return call(function () use ($exception, $request, $response) {
            if (empty($this->handlers)) {
                throw $exception;
            }

            /** @var ExceptionHandler $handler */
            foreach ($this->handlers as $handler) {
                if ($handler->getExceptionClass() === get_class($exception)) {
                    $injector = new ContextInjector($handler->getCallable(), $request, $response, $exception);

                    return yield $injector->inject();
                }
            }

            throw $exception;
        });
    }
}