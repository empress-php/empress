<?php

declare(strict_types=1);

namespace Empress\Routing\Path;

final class RegexBuilder
{
    public function buildRegex(Path $path): string
    {
        $regexParts = [];

        foreach ($path->getParts() as $part) {
            if ($part === '*') {
                $regexParts[] = '([^/]*)';
            } elseif (\mb_strlen($part) > 1 && \mb_strpos($part, ':') !== false) {
                $paramName = \mb_substr($part, 1);

                $regexParts[] = '(?<' . $paramName . '>.+?)';
            } else {
                $regexParts[] = $part;
            }
        }

        return '~^/' . \implode('/', $regexParts) . '/?$~';
    }
}
