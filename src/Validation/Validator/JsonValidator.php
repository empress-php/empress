<?php

namespace Empress\Validation\Validator;

class JsonValidator implements ValidatorInterface
{
    public function validate(mixed $value): array
    {
        try {
            return \json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ValidatorException($e->getMessage());
        }
    }
}
