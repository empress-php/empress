<?php

namespace Empress\Validation\Validator;

class FloatValidator implements ValidatorInterface
{
    public function validate(mixed $value): float
    {
        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($filtered === false) {
            throw new ValidatorException(\sprintf(
                'Value %s could not be converted to float.',
                $value
            ));
        }

        return $filtered;
    }
}