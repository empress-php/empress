<?php

namespace Empress\Routing\Path;

class RegexBuilder
{
    private string $regex;

    public function __construct(Path $path)
    {
        $this->regex = $this->buildRegex($path);
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    private function buildRegex(Path $path): string
    {
        $regexParts = [];

        foreach ($path->getParts() as $part) {
            if ($part === '*') {
                $regexParts[] = '(.+?)';
            } elseif (mb_strlen($part) > 1 && mb_strpos($part, ':') !== false) {
                $paramName = mb_substr($part, 1);

                $regexParts[] = '(?<' . $paramName . '>.+?)';
            } else {
                $regexParts[] = $part;
            }
        }

        return  '~^/' . implode('/', $regexParts) . '/?$~';
    }
}