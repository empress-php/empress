<?php

namespace Empress\Test\Validation;

use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;
use Empress\Validation\WrappedValue;
use PHPUnit\Framework\TestCase;

class WrappedValueTest extends TestCase
{
    public function testSuccessfulUnwrap(): void
    {
        $value = 10;
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willReturn($value);

        $wrappedValue = new WrappedValue($value, $validator);

        static::assertEquals($value, $wrappedValue->unwrap());
    }

    public function testFailedUnwrap(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willThrowException(new ValidatorException());

        $wrappedValue = new WrappedValue(null, $validator);

        $wrappedValue->unwrap();
    }

    public function testUnwrapOrNull(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willThrowException(new ValidatorException());

        $wrappedValue = new WrappedValue('abc', $validator);

        static::assertNull($wrappedValue->unwrapOrNull());
    }

    public function testUnwrapOr(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willThrowException(new ValidatorException());

        $wrappedValue = new WrappedValue(null, $validator);

        static::assertEquals(10, $wrappedValue->unwrapOr(10));
    }

    public function testUnwrapOrThrow(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willThrowException(new ValidatorException());

        $wrappedValue = new WrappedValue(null, $validator);

        $wrappedValue->unwrapOrThrow(new \InvalidArgumentException());
    }
}
