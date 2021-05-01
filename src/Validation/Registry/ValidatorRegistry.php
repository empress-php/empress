<?php

namespace Empress\Validation\Registry;

use Empress\Validation\ValidationContext;
use Empress\Validation\Validator\ValidatorInterface;

class ValidatorRegistry
{
    private array $validators = [];

    public function register(string $name, ValidatorInterface $validator): void
    {
        if (isset($this->validators[$name])) {
            throw new ValidatorRegistryException(\sprintf(
                'Validator named "%s" of type %s already registered.',
                $name,
                $validator::class
            ));
        }

        $this->validators[$name] = $validator;
    }

    public function get(string $name): ValidatorInterface
    {
        if (!isset($this->validators[$name])) {
            throw new ValidatorRegistryException(\sprintf(
                'Validator named "%s" not found.',
                $name
            ));
        }

        return $this->validators[$name];
    }

    /**
     * @template T
     * @param T $value
     * @return ValidationContext<T>
     */
    public function contextFor(mixed $value): ValidationContext
    {
        return new ValidationContext($value, $this);
    }
}
