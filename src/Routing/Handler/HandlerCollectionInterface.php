<?php

declare(strict_types=1);

namespace Empress\Routing\Handler;

interface HandlerCollectionInterface
{
    public function add(HandlerEntry $entry): void;

    public function filterByPath(string $path): static;

    public function filterByType(int $type): static;

    public function first(): ?HandlerEntry;

    public function count(): int;

    public function merge(self $handlerCollection): self;

    public function getEntries(): array;
}
