<?php

namespace Empress\Routing\Status;

use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Routing\MapperInterface;
use function Amp\call;

class StatusMapper implements MapperInterface
{

    /**
     * @var StatusHandler[]
     */
    private array $handlers = [];

    /**
     * @param StatusHandler $handler
     */
    public function addHandler(StatusHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function process(ContextInjector $injector): Promise
    {
        return call(function () use ($injector) {
            $request = $injector->getRequest();
            $response = $injector->getResponse();
            $statusCode = $injector->getResponse()->getStatus();
            $candidates = [];

            foreach ($this->handlers as $handler) {
                if ($handler->getStatus() === $statusCode) {
                    if ($handler->hasHeaders()) {
                        if ($handler->satisfiesHeaders($request)) {
                            return yield $injector->inject($handler->getCallable());
                        }
                    } else {
                        $candidates[$statusCode] = $handler;
                    }
                }
            }

            if (isset($candidates[$statusCode])) {
                $candidate = $candidates[$statusCode];
                return yield $injector->inject($candidate->getCallable());
            }

            return $response;
        });
    }
}
