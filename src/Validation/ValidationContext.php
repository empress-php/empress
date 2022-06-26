<?php

declare(strict_types=1);

namespace Empress\Validation;

use Empress\Validation\Registry\ValidatorRegistryInterface;
use Empress\Validation\Validator\NoopValidator;

/**
 * @template T
 */
final class ValidationContext
{
    /**
     * @param T $value
     */
    public function __construct(
        private mixed $value,
        private ValidatorRegistryInterface $registry
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
