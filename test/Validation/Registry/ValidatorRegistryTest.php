<?php

namespace Empress\Test\Validation\Registry;

use Empress\Validation\Registry\ValidatorRegistry;
use Empress\Validation\Registry\ValidatorRegistryException;
use Empress\Validation\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidatorRegistryTest extends TestCase
{
    public function testValidatorIsRegistered(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $registry = new ValidatorRegistry();

        $registry->register('validator', $validator);

        static::assertEquals($validator, $registry->get('validator'));
    }

    public function testAllowSingleValidatorInstanceForGivenName(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $validator = $this->createMock(ValidatorInterface::class);
        $registry = new ValidatorRegistry();

        $registry->register('validator', $validator);
        $registry->register('validator', $validator);
    }

    public function testNonexistentValidator(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $registry = new ValidatorRegistry();

        $registry->get('validator');
    }

    public function testContextForNonexistentValidator(): void
    {
        $this->expectException(ValidatorRegistryException::class);

        $registry = new ValidatorRegistry();
        $context = $registry->contextFor('abc');

        $context->to('validator');
    }
}
