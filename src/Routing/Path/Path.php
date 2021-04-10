<?php

namespace Empress\Routing\Path;

class Path
{

    /**
     * @var string[]
     */
    private array $parts;

    public function __construct(private string $path)
    {
        $this->parts = $this->toParts($path);
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function __toString(): string
    {
        return $this->path;
    }

    private function toParts(string $path): array
    {
        return \array_values(\array_filter(\explode('/', $path)));
    }
}
