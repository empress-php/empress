<?php

declare(strict_types=1);

namespace Empress\Validation;

use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;

/**
 * @template T
 * @template U
 */
final class WrappedValue
{
    /**
     * @var array<array{callback: callable, message: string}>
     */
    private array $checks = [];

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
        $result = $this->validator->validate($this->value);

        if (!empty($this->checks)) {
            $errors = [];

            foreach ($this->checks as ['callback' => $callback, 'message' => $message]) {
                if (!$callback($result)) {
                    $errors[] = new ValidatorException($message);
                }
            }

            if (!empty($errors)) {
                throw ValidatorException::collect($errors);
            }
        }

        return $result;
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

    /**
     * @template V
     * @param callable(T, ValidatorException): V $callback
     * @return U|V
     */
    public function unwrapOrFn(callable $callback): mixed
    {
        try {
            return $this->unwrap();
        } catch (ValidatorException $e) {
            return $callback($this->value, $e);
        }
    }

    /**
     * @param callable(U): bool $callback
     */
    public function check(callable $callback, string $message = ''): static
    {
        $this->checks[] = [
            'callback' => $callback,
            'message' => $message,
        ];

        return $this;
    }
}
