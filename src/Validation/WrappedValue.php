<?php

namespace Empress\Validation;

use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;

/**
 * @template T
 */
class WrappedValue
{

    /**
     * @param T $value
     */
    public function __construct(
        private mixed $value,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @return T
     * @throws ValidatorException
     */
    public function unwrap(): mixed
    {
        return $this->validator->validate($this->value);
    }

    /**
     * @return T|null
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
     * @template U
     * @param U $value
     * @return T|U
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
