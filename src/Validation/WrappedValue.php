<?php

namespace Empress\Validation;

use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;

/**
 * @template T
 * @template U
 */
class WrappedValue
{

    /**
     * @param T $value
     * @param ValidatorInterface<T, U> $validator
     */
    public function __construct(
        private mixed $value,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @return U
     * @throws ValidatorException
     */
    public function unwrap(): mixed
    {
        return $this->validator->validate($this->value);
    }

    /**
     * @return U|null
     */
    public function unwrapOrNull(): mixed
    {
        try {
            return $this->unwrap();
        } catch (ValidatorException) {
            return null;
        }
    }

    /**
     * @template V
     * @param V $value
     * @return U|V
     */
    public function unwrapOr(mixed $value): mixed
    {
        try {
            return $this->unwrap();
        } catch (ValidatorException) {
            return $value;
        }
    }

    /**
     * @return U
     * @throws \Throwable
     */
    public function unwrapOrThrow(\Throwable $exception): mixed
    {
        try {
            return $this->unwrap();
        } catch (ValidatorException) {
            throw $exception;
        }
    }
}
