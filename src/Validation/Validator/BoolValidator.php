<?php

namespace Empress\Validation\Validator;

class BoolValidator implements ValidatorInterface
{
    public function validate(mixed $value): bool
    {
        $filtered = \filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filtered === null) {
            throw new ValidatorException(\sprintf(
                'Value %s could not be converted to bool.',
                $value
            ));
        }

        return $filtered;
    }
}