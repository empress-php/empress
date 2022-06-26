<?php

declare(strict_types=1);

namespace Empress\Test\Validation;

use Empress\Validation\Registry\ValidatorRegistryInterface;
use Empress\Validation\ValidationContext;
use Empress\Validation\Validator\NoopValidator;
use Empress\Validation\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

final class ValidationContextTest extends TestCase
{
    public function testCorrectValidatorIsChosen(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $registry = $this->createMock(ValidatorRegistryInterface::class);
        $registry
            ->expects(self::once())
            ->method('get')
            ->with('validator')
            ->willReturn($validator);

        $context = new ValidationContext(null, $registry);

        $context->to('validator');
    }

    public function testUnsafeUnwrap(): void
    {
        $registry = $this->createMock(ValidatorRegistryInterface::class);
        $context = new ValidationContext('abc', $registry);

        self::assertSame('abc', $context->unsafeUnwrap());
    }

    public function testPass(): void
    {
        $registry = $this->createMock(ValidatorRegistryInterface::class);
        $registry
            ->expects(self::once())
            ->method('get')
            ->with(NoopValidator::class);

        $context = new ValidationContext(10, $registry);

        $context->pass();
    }
}
