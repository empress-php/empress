<?php

namespace Empress\Internal;

trait TypeAssertionTrait
{
    private function assertInstanceOf(string $expectedType, $var): void
    {
        $actualType = is_object($var) ? get_class($var) : gettype($var);

        if ($expectedType !== $actualType) {
            throw new TypeError(sprintf('Expected an instance of %s but %s was given.', $expectedType, $actualType));
        }
    }
}
