<?php

namespace Empress\Routing;

use InvalidArgumentException;

class HandlerGroup
{

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array<HandlerEntry>
     */
    private $entries = [];

    /**
     * RouteGroup constructor.
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function addBeforeFilter(callable $handler, string $path = '*')
    {
        $this->addEntry(new HandlerEntry(
            HandlerType::BEFORE,
            $path,
            $handler
        ));
    }

    public function addAfterFilter(callable $handler, string $path = '*')
    {
        $this->addEntry(new HandlerEntry(
            HandlerType::AFTER,
            $path,
            $handler
        ));
    }

    public function addRoute(int $type, string $path, callable $handler)
    {
        $this->addEntry(new HandlerEntry(
            $type,
            $path,
            $handler
        ));
    }

    public function merge(HandlerGroup $group)
    {
        $entries = $group->getEntries();

        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    private function addEntry(HandlerEntry $entry)
    {
        $type = $entry->getType();
        $path = $entry->getPath();

        if (HandlerType::isFilter($type) && strpos($path, ':') !== false) {
            throw new InvalidArgumentException('No named parameters allowed for filters');
        }

        $entry->setPath(rtrim($this->prefix, '/') . '/' . ltrim($entry->getPath(), '/'));
        $this->entries[] = $entry;
    }
}
