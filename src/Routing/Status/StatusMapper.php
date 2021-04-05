<?php

namespace Empress\Routing\Status;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Internal\ContextInjector;
use function Amp\call;

class StatusMapper
{

    /**
     * @var array<StatusHandler>
     */
    private $handlers = [];

    /**
     * @param StatusHandler $handler
     */
    public function addHandler(StatusHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, Response $response): Promise
    {
        return call(function () use ($request, $response) {
            $statusCode = $response->getStatus();
            $candidates = [];

            /** @var StatusHandler $handler */
            foreach ($this->handlers as $handler) {
                if ($handler->getStatus() === $statusCode) {
                    if ($handler->hasHeaders()) {
                        if ($handler->satisfiesHeaders($request)) {
                            $injector = new ContextInjector($handler->getCallable(), $request, $response);

                            return yield $injector->inject();
                        }
                    } else {
                        $candidates[$statusCode] = $handler;
                    }
                }
            }

            if (isset($candidates[$statusCode])) {
                $candidate = $candidates[$statusCode];
                $injector = new ContextInjector($candidate->getCallable(), $request, new Response($statusCode));

                return yield $injector->inject();
            }

            return $response;
        });
    }
}
