<?php

declare(strict_types=1);

namespace Empress\Validation\Registry;

use Empress\Validation\ValidationContext;
use Empress\Validation\Validator\ValidatorInterface;

interface ValidatorRegistryInterface
{
    public function register(string $name, ValidatorInterface $validator): void;

    public function get(string $name): ValidatorInterface;

    /**
     * @template T
     * @param T $value
     * @return ValidationContext<T>
     */
    public function contextFor(mixed $value): ValidationContext;
}
