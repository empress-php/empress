<?php

namespace Empress\Test\Validation;

use Empress\Validation\Registry\ValidatorRegistry;
use Empress\Validation\ValidationContext;
use Empress\Validation\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidationContextTest extends TestCase
{
    public function testCorrectValidatorIsChosen(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $registry = $this->createMock(ValidatorRegistry::class);
        $registry
            ->expects(static::once())
            ->method('get')
            ->with('validator')
            ->willReturn($validator);

        $context = new ValidationContext(null, $registry);

        $context->to('validator');
    }

    public function testUnsafeUnwrap(): void
    {
        $registry = $this->createMock(ValidatorRegistry::class);
        $context = new ValidationContext('abc', $registry);

        static::assertEquals('abc', $context->unsafeUnwrap());
    }
}
