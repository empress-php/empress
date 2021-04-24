<?php

namespace Empress\Validation\Validator;

interface ValidatorInterface
{

    /**
     * @throws ValidatorException
     */
    public function validate(mixed $value): mixed;
}
