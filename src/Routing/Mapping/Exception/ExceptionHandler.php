<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping\Exception;

use Empress\Routing\Mapping\ContentTypeAwareHandlerInterface;

final class ExceptionHandler implements ContentTypeAwareHandlerInterface
{
    /**
     * @param callable $callable
     */
    public function __construct(
        private readonly mixed $callable,
        private readonly string $exceptionClass,
        private readonly ?string $contentType = null
    ) {
    }
    
    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function hasContentType(): bool
    {
        return $this->contentType !== null;
    }
}
