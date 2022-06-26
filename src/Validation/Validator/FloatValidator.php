<?php

declare(strict_types=1);

namespace Empress\Validation\Validator;

final class FloatValidator implements ValidatorInterface
{
    public function validate(mixed $value): float
    {
        $filtered = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        if ($filtered === false) {
            throw new ValidatorException(\sprintf(
                'Value %s could not be converted to float.',
                $value
            ));
        }

        return $filtered;
    }
}
