<?php

namespace Empress\Validation\Registry;

use Empress\Validation\Validator\IntValidator;

class DefaultValidatorRegistry extends ValidatorRegistry
{
    public function __construct()
    {
        $this->register('int', new IntValidator());
    }
}
