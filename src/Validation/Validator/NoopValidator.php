<?php

namespace Empress\Validation\Validator;

class NoopValidator implements ValidatorInterface
{
    public function validate(mixed $value): mixed
    {
        return $value;
    }
}
