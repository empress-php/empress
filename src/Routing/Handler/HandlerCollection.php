<?php

namespace Empress\Routing\Handler;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class HandlerCollection implements IteratorAggregate
{
    public function __construct(private array $entries = [])
    {
    }

    public function add(HandlerEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    public function filterByPath(string $path): self
    {
        $entries = \array_filter($this->entries, function (HandlerEntry $entry) use ($path) {
            $matcher = $entry->getPathMatcher();

            return $matcher->matches($path);
        });

        return new self($entries);
    }

    public function filterByType(int $type): self
    {
        $entries = \array_filter($this->entries, fn (HandlerEntry $entry) => $entry->getType() === $type);

        return new self($entries);
    }

    public function first(): ?HandlerEntry
    {
        $entry = \reset($this->entries);

        return $entry ?: null;
    }

    public function count(): int
    {
        return \count($this->entries);
    }

    public function merge(HandlerCollection $handlerCollection): HandlerCollection
    {
        return new self(\array_merge($this->entries, $handlerCollection->entries));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }
}
