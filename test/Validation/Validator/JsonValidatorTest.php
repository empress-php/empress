<?php

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\JsonValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

class JsonValidatorTest extends TestCase
{
    public function testValidateValidJson(): void
    {
        $validator = new JsonValidator();

        static::assertEquals([
            'a' => 123,
            'b' => 456
        ], $validator->validate('{"a":123,"b":456}'));
    }

    public function testValidateInvalidJson(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new JsonValidator();

        $validator->validate(\INF);
    }
}
