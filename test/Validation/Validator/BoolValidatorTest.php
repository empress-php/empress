<?php

declare(strict_types=1);

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\BoolValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

final class BoolValidatorTest extends TestCase
{
    public function testBoolValue(): void
    {
        $validator = new BoolValidator();
        $true = $validator->validate('true');
        $false = $validator->validate('0');

        self::assertTrue($true);
        self::assertFalse($false);
    }

    public function testNonBoolValue(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new BoolValidator();

        $validator->validate('123');
    }
}
