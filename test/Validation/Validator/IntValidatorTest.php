<?php

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\IntValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

class IntValidatorTest extends TestCase
{
    public function testValidateValidInt(): void
    {
        $validator = new IntValidator();

        static::assertEquals(0, $validator->validate('0'));
    }

    public function testValidateInvalidInt(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new IntValidator();

        $validator->validate('abc');
    }
}
