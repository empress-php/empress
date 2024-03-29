<?php

declare(strict_types=1);

namespace Empress\Routing\Path;

final class Path
{
    /**
     * @var string[]
     */
    private array $parts;

    public function __construct(private string $path)
    {
        $this->parts = $this->toParts($path);
    }

    public function __toString(): string
    {
        return $this->path;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    private function toParts(string $path): array
    {
        return \array_values(\array_filter(\explode('/', $path)));
    }
}
