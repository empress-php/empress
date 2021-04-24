<?php

namespace Empress\Validation;

use Empress\Validation\Registry\ValidatorRegistry;

/**
 * @template T
 */
class ValidationContext
{

    /**
     * @param T $value
     */
    public function __construct(
        private mixed $value,
        private ValidatorRegistry $registry
    ) {
    }

    /**
     * @return WrappedValue<T>
     */
    public function to(string $validatorName): WrappedValue
    {
        return new WrappedValue($this->value, $this->registry->get($validatorName));
    }
}
