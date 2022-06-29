<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping\Status;

use Amp\Promise;
use Empress\Internal\ContextInjector;
use Empress\Routing\Mapping\ContentTypeMatcher;
use Empress\Routing\Mapping\MapperInterface;
use function Amp\call;

final class StatusMapper implements MapperInterface
{
    /**
     * @var StatusHandler[]
     */
    private array $handlers = [];

    public function __construct(private readonly ContentTypeMatcher $contentTypeMatcher)
    {
    }
    
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
                    if ($handler->hasContentType()) {
                        if ($this->contentTypeMatcher->match($handler, $request)) {
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
