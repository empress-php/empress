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

    public function testUnwrapOrFn(): void
    {
        $exception = new ValidatorException();

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willThrowException($exception);

        $wrappedValue = new WrappedValue(null, $validator);

        $wrappedValue->unwrapOrFn(function (mixed $value, \Throwable $e) use ($exception) {
            static::assertNull($value);
            static::assertEquals($exception, $e);
        });
    }

    public function testSingleCheck(): void
    {
        $i = 10;
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willReturn($i);

        $wrappedValue = new WrappedValue($i, $validator);

        $value = $wrappedValue
            ->check(fn ($value) => $value === $i)
            ->unwrap();

        static::assertEquals($i, $value);
    }

    public function testFailedCheck(): void
    {
        $this->expectException(ValidatorException::class);

        $validator = $this->createMock(ValidatorInterface::class);

        $wrappedValue = new WrappedValue(null, $validator);

        $wrappedValue
            ->check(fn ($value) => $value !== null)
            ->unwrap();
    }

    public function testMultipleChecks(): void
    {
        $i = 10;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willReturn($i);

        $wrappedValue = new WrappedValue($i, $validator);

        $value = $wrappedValue
            ->check(fn ($value) => $value <= 10)
            ->check(fn ($value) => $value > 1)
            ->check(fn ($value) => !\is_float($value))
            ->unwrap();

        static::assertEquals($i, $value);
    }

    public function testMultipleFailedChecks(): void
    {
        $i = 10;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->willReturn($i);

        $wrappedValue = new WrappedValue($i, $validator);

        $wrappedValue
            ->check(fn ($value) => $value > 10, '1')
            ->check(fn ($value) => $value < 1, '2')
            ->check(fn ($value) => \is_float($value), '3')
            ->unwrapOrFn(function (mixed $_, ValidatorException $e) {
                $exceptions = $e->getExceptions();

                static::assertNotEmpty($exceptions);
                static::assertEquals('1', $exceptions[0]->getMessage());
                static::assertEquals('2', $exceptions[1]->getMessage());
                static::assertEquals('3', $exceptions[2]->getMessage());
            });
    }
}
