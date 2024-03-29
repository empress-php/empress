<?php

declare(strict_types=1);

namespace Empress\Test\Validation\Validator;

use Empress\Validation\Validator\DateTimeValidator;
use Empress\Validation\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

final class DateTimeValidatorTest extends TestCase
{
    public function testDateTimeValue(): void
    {
        $validator = new DateTimeValidator();
        $dateTime = $validator->validate('2005-4-5T21:37:00');

        self::assertSame(1112737020, $dateTime->getTimestamp());
    }

    public function testNonDateTimeValue(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = new DateTimeValidator();

        $validator->validate(0);
    }
}
