<?php

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\BoolValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

class BoolValidatorTest extends TestCase
{
    public function testBoolValue(): void
    {
        $validator = new BoolValidator();
        $true = $validator->validate('true');
        $false = $validator->validate('0');

        static::assertTrue($true);
        static::assertFalse($false);
    }

    public function testNonBoolValue(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new BoolValidator();

        $validator->validate('123');
    }
}
