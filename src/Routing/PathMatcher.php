<?php

namespace Empress\Routing;

use InvalidArgumentException;
use SplObjectStorage;

class PathMatcher
{

    /**
     * @var array<HandlerEntry>
     */
    private $entries = [];

    /**
     * @var SplObjectStorage
     */
    private $parsers;

    public function __construct()
    {
        $this->parsers = new SplObjectStorage();
    }

    public function addEntries(HandlerGroup $group)
    {

        /** @var HandlerEntry $entry */
        foreach ($group->getEntries() as $entry) {
            $type = $entry->getType();
            $path = $entry->getPath();

            if (HandlerType::isHttpMethod($type) && isset($this->entries[$type][$path])) {
                throw new InvalidArgumentException(sprintf(
                    '%s handler for path: %s already present.',
                    HandlerType::toString($type),
                    $path
                ));
            }

            $this->entries[$type][$path] = $entry;
            $this->parsers->attach($entry, new PathParser($path));
        }
    }

    /**
     * @param int $type
     * @param string $path
     * @return array<HandlerEntry>
     */
    public function findEntries(int $type, string $path): array
    {
        if (empty($this->entries[$type])) {
            return [];
        }

        return array_filter($this->entries[$type], function (HandlerEntry $entry) use ($path) {
            return !empty($this->match($entry, $path));
        });
    }

    /**
     * @return bool
     */
    public function hasEntries(): bool
    {
        return !empty($this->entries);
    }

    public function getPathParams(HandlerEntry $entry, string $path)
    {
        return array_filter(
            $this->match($entry, $path) ?? [],
            'is_string',
            ARRAY_FILTER_USE_KEY
        );
    }

    private function match(HandlerEntry $entry, string $toMatch)
    {

        /** @var PathParser $parser */
        $parser = $this->parsers[$entry];

        return $parser->match($toMatch);
    }
}
