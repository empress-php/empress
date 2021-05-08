<?php

namespace Empress\Validation\Registry;

use Empress\Validation\Validator\BoolValidator;
use Empress\Validation\Validator\DateTimeValidator;
use Empress\Validation\Validator\FloatValidator;
use Empress\Validation\Validator\IntValidator;
use Empress\Validation\Validator\JsonValidator;
use Empress\Validation\Validator\NoopValidator;

class DefaultValidatorRegistry extends ValidatorRegistry
{
    public function __construct()
    {
        $this->register('int', new IntValidator());
        $this->register('float', new FloatValidator());
        $this->register('bool', new BoolValidator());
        $this->register('json', new JsonValidator());
        $this->register(\DateTime::class, new DateTimeValidator());
        $this->register(NoopValidator::class, new NoopValidator());
    }
}
