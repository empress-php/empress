<?php

namespace Empress\Routing\Path;

class PathMatcher
{
    private RegexBuilder $regexBuilder;

    public function __construct(RegexBuilder $regexBuilder)
    {
        $this->regexBuilder = $regexBuilder;
    }

    public function matches(string $toMatch): bool
    {
        $regex = $this->regexBuilder->getRegex();

        return \preg_match($regex, $toMatch) === 1;
    }

    public function extractNamedParams(string $toMatch): array
    {
        $regex = $this->regexBuilder->getRegex();
        \preg_match($regex, $toMatch, $matches);

        return \array_filter($matches, fn (mixed $key) => \is_string($key), ARRAY_FILTER_USE_KEY);
    }

    public function extractWildcards(string $toMatch): array
    {
        $regex = $this->regexBuilder->getRegex();
        \preg_match($regex, $toMatch, $matches);

        return \array_values(
            \array_filter(
                \array_splice($matches, 1),
                fn (mixed $key) => \is_int($key),
                ARRAY_FILTER_USE_KEY
            )
        );
    }
}
