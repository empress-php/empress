<?php

namespace Empress\Routing\Path;

class RegexBuilder
{
    public function __construct(private Path $path)
    {
    }

    public function buildRegex(): string
    {
        $regexParts = [];

        foreach ($this->path->getParts() as $part) {
            if ($part === '*') {
                $regexParts[] = '([^/]*)';
            } elseif (\mb_strlen($part) > 1 && \mb_strpos($part, ':') !== false) {
                $paramName = \mb_substr($part, 1);

                $regexParts[] = '(?<' . $paramName . '>.+?)';
            } else {
                $regexParts[] = $part;
            }
        }

        return  '~^/' . \implode('/', $regexParts) . '/?$~';
    }
}
