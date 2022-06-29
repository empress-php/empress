<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping;

interface ContentTypeAwareHandlerInterface
{
    public function getContentType(): ?string;

    public function hasContentType(): bool;
}
