<?php

declare(strict_types=1);

namespace Empress\Validation\Registry;

use Empress\Validation\ValidationContext;
use Empress\Validation\Validator\ValidatorInterface;

abstract class AbstractValidatorRegistry implements ValidatorRegistryInterface
{
    private array $validators = [];

    final public function register(string $name, ValidatorInterface $validator): void
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

    final public function get(string $name): ValidatorInterface
    {
        if (!isset($this->validators[$name])) {
            throw new ValidatorRegistryException(\sprintf(
                'Validator named "%s" not found.',
                $name
            ));
        }

        return $this->validators[$name];
    }

    final public function contextFor(mixed $value): ValidationContext
    {
        return new ValidationContext($value, $this);
    }
}
