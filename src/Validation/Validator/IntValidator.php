<?php

namespace Empress\Validation\Validator;

class IntValidator implements ValidatorInterface
{
    public function validate(mixed $value): int
    {
        $value = \filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new ValidatorException();
        }

        return $value;
    }
}
