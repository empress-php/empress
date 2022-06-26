<?php

declare(strict_types=1);

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\FloatValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

final class FloatValidatorTest extends TestCase
{
    public function testFloatValue(): void
    {
        $validator = new FloatValidator();
        $value = $validator->validate('0.0001');

        self::assertSame(0.0001, $value);
    }

    public function testNonFloatValue(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new FloatValidator();

        $validator->validate('x');
    }
}
