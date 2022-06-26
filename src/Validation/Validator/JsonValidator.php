<?php

declare(strict_types=1);

namespace Empress\Validation\Validator;

final class JsonValidator implements ValidatorInterface
{
    public function validate(mixed $value): array
    {
        try {
            return \json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException|\Error $e) {
            throw new ValidatorException($e->getMessage());
        }
    }
}
