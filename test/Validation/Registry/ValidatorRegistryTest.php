<?php

declare(strict_types=1);

namespace Empress\Test\Validation\Registry;

use Empress\Validation\Registry\AbstractValidatorRegistry;
use Empress\Validation\Registry\ValidatorRegistryException;
use Empress\Validation\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

final class ValidatorRegistryTest extends TestCase
{
    public function testValidatorIsRegistered(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $registry = new class() extends AbstractValidatorRegistry {
        };

        $registry->register('validator', $validator);

        self::assertSame($validator, $registry->get('validator'));
    }

    public function testAllowSingleValidatorInstanceForGivenName(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $validator = $this->createMock(ValidatorInterface::class);
        $registry = new class() extends AbstractValidatorRegistry {
        };

        $registry->register('validator', $validator);
        $registry->register('validator', $validator);
    }

    public function testNonexistentValidator(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $registry = new class() extends AbstractValidatorRegistry {
        };

        $registry->get('validator');
    }

    public function testContextForNonexistentValidator(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $registry = new class() extends AbstractValidatorRegistry {
        };

        $context = $registry->contextFor('abc');

        $context->to('validator');
    }
}
