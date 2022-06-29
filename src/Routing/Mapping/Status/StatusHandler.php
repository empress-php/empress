<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping\Status;

use Empress\Routing\Mapping\ContentTypeAwareHandlerInterface;

final class StatusHandler implements ContentTypeAwareHandlerInterface
{

    /**
     * @param callable $callable
     */
    public function __construct(
        private readonly mixed $callable,
        private readonly int $status,
        private readonly ?string $contentType = null
    ) {
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function hasContentType(): bool
    {
        return $this->contentType !== null;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
