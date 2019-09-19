<?php

namespace Empress\Internal;

class CaseConverter
{

    /** @var array */
    private $parsed;

    public function __construct(string $str)
    {
        // Check if it's kebab or case snake
        $matches = [];
        preg_match('/[\-_]/', $str, $matches);

        if (count($matches) > 0) {
            $this->parsed = preg_split('/[\-_]/', strtolower($str));

            return;
        }

        // Otherwise, try parsing it as camelCase or PascalCase
        $parsed = explode(' ', strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $str)));

        if ($parsed !== false && count($parsed) > 0) {
            $this->parsed = $parsed;

            return;
        }

        throw new \InvalidArgumentException(sprintf('String "%s" is not convertible', $str));
    }

    public function kebabCasify(): string
    {
        return implode('-', $this->parsed);
    }

    public function snakeCasify(): string
    {
        return implode('_', $this->parsed);
    }

    public function pascalCasify(): string
    {
        return implode('', array_map('ucfirst', $this->parsed));
    }

    public function camelCasify(): string
    {
        $head = $this->parsed[0];
        return $head . implode('', array_map('ucfirst', array_slice($this->parsed, 1)));
    }
}
