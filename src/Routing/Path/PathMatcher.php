<?php

namespace Empress\Routing\Path;

class PathMatcher
{
    private string $regex;

    public function __construct(private RegexBuilder $regexBuilder)
    {
        $this->regex = $this->regexBuilder->buildRegex();
    }

    public function matches(string $toMatch): bool
    {
        return \preg_match($this->regex, $toMatch) === 1;
    }

    public function extractNamedParams(string $toMatch): array
    {
        \preg_match($this->regex, $toMatch, $matches);

        return \array_filter($matches, fn (mixed $key) => \is_string($key), ARRAY_FILTER_USE_KEY);
    }

    public function extractWildcards(string $toMatch): array
    {
        \preg_match($this->regex, $toMatch, $matches);

        return \array_values(
            \array_filter(
                \array_splice($matches, 1),
                fn (mixed $key) => \is_int($key),
                ARRAY_FILTER_USE_KEY
            )
        );
    }
}
