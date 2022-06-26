<?php

declare(strict_types=1);

namespace Empress\Validation\Validator;

final class NoopValidator implements ValidatorInterface
{
    public function validate(mixed $value): mixed
    {
        return $value;
    }
}
