<?php

namespace Empress\Validation;

use Empress\Validation\Registry\ValidatorRegistry;
use Empress\Validation\Validator\NoopValidator;

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
     * @template U
     * @return WrappedValue<T, U>
     */
    public function to(string $validatorName): WrappedValue
    {
        return new WrappedValue($this->value, $this->registry->get($validatorName));
    }

    /**
     * @return WrappedValue<T, T>
     */
    public function pass(): WrappedValue
    {
        return new WrappedValue($this->value, $this->registry->get(NoopValidator::class));
    }

    /**
     * @return T
     */
    public function unsafeUnwrap(): mixed
    {
        return $this->value;
    }
}
