<?php

namespace Empress\Routing;

use InvalidArgumentException;
use SplObjectStorage;

class PathMatcher
{

    /**
     * @var HandlerEntry[]
     */
    private array $entries = [];

    private SplObjectStorage $parsers;

    public function __construct()
    {
        $this->parsers = new SplObjectStorage();
    }

    public function addEntry(HandlerEntry $entry): void
    {
        $type = $entry->getType();
        $path = $entry->getPath();
        $pathString = (string) $path;
        $index = HandlerType::toString($type) . $pathString;

        /** @var HandlerEntry $existingHandler */
        $existingHandler = $this->entries[$index] ?? null;

        if (HandlerType::isHttpMethod($type) && $existingHandler !== null && $existingHandler->getType() === $type) {
            throw new InvalidArgumentException(\sprintf(
                '%s handler for path: %s already present.',
                HandlerType::toString($type),
                $pathString
            ));
        }

        $this->entries[$index] = $entry;
        $this->parsers->attach($entry, new PathParser($path));
    }

    public function merge(PathMatcher $pathMatcher): void
    {
        foreach ($pathMatcher->getEntries() as $entry) {
            $this->addEntry($entry);
        }
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param string $path
     * @return array<HandlerEntry>
     */
    public function findEntries(string $path, ?int $type = null): array
    {
        if (!$this->hasEntries()) {
            return [];
        }

        return \array_filter($this->entries, function (HandlerEntry $entry) use ($path, $type) {
            return !empty($this->match($entry, $path)) && ($type !== null ? $entry->getType() === $type : true);
        });
    }

    /**
     * @return bool
     */
    public function hasEntries(): bool
    {
        return !empty($this->entries);
    }

    public function getPathParams(HandlerEntry $entry, string $path): array
    {
        return \array_filter(
            $this->match($entry, $path) ?? [],
            'is_string',
            ARRAY_FILTER_USE_KEY
        );
    }

    private function match(HandlerEntry $entry, string $toMatch): ?array
    {

        /** @var PathParser $parser */
        $parser = $this->parsers[$entry];

        return $parser->match($toMatch);
    }
}
