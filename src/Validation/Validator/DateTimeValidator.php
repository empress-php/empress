<?php

namespace Empress\Validation\Validator;

class DateTimeValidator implements ValidatorInterface
{
    public function validate(mixed $value): \DateTime
    {
        try {
            $dateTime = new \DateTime($value);
        } catch (\Exception $e) {
            throw new ValidatorException($e->getMessage());
        }

        return $dateTime;
    }
}
