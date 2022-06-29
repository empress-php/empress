<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping\Exception;

use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Empress\Internal\ContextInjector;
use Empress\Routing\Mapping\ContentTypeMatcher;
use Empress\Routing\Mapping\MapperInterface;
use function Amp\call;

final class ExceptionMapper implements MapperInterface
{
    /**
     * @var ExceptionHandler[]
     */
    private array $handlers = [];

    public function __construct(private readonly ContentTypeMatcher $contentTypeMatcher)
    {
    }

    public function addHandler(ExceptionHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function process(ContextInjector $injector): Promise
    {
        return call(function () use ($injector) {
            $request = $injector->getRequest();
            $exception = $injector->getThrowable();
            $candidates = [];

            if ($exception === null) {
                return new Success();
            }

            if (empty($this->handlers)) {
                throw $exception;
            }

            foreach ($this->handlers as $handler) {
                $exceptionClass = $handler->getExceptionClass();

                if ($exceptionClass === $exception::class) {
                    if ($handler->hasContentType()) {
                        if ($this->contentTypeMatcher->match($handler, $request)) {
                            return yield $injector->inject($handler->getCallable());
                        }
                    } else {
                        $candidates[$exceptionClass] = $handler;
                    }
                }
            }

            if (isset($candidates[$exception::class])) {
                $candidate = $candidates[$exception::class];

                return yield $injector->inject($candidate->getCallable());
            }

            throw $exception;
        });
    }
}
