<?php

declare(strict_types=1);

namespace Empress\Routing\Exception;

use Amp\Promise;
use Amp\Success;
use Empress\Internal\ContextInjector;
use Empress\Routing\MapperInterface;
use function Amp\call;

final class ExceptionMapper implements MapperInterface
{
    /**
     * @var ExceptionHandler[]
     */
    private array $handlers = [];

    public function addHandler(ExceptionHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function process(ContextInjector $injector): Promise
    {
        return call(function () use ($injector) {
            $exception = $injector->getThrowable();

            if ($exception === null) {
                return new Success();
            }

            if (empty($this->handlers)) {
                throw $exception;
            }

            foreach ($this->handlers as $handler) {
                if ($handler->getExceptionClass() === $exception::class) {
                    return yield $injector->inject($handler->getCallable());
                }
            }

            throw $exception;
        });
    }
}
