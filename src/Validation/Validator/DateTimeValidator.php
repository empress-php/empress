<?php

declare(strict_types=1);

namespace Empress\Validation\Validator;

final class DateTimeValidator implements ValidatorInterface
{
    public function validate(mixed $value): \DateTimeImmutable
    {
        try {
            $dateTime = new \DateTimeImmutable($value);
        } catch (\Exception|\Error $e) {
            throw new ValidatorException($e->getMessage());
        }

        return $dateTime;
    }
}
