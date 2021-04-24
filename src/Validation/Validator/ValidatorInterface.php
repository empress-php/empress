<?php

namespace Empress\Validation\Validator;

/**
 * @template T
 * @template U
 */
interface ValidatorInterface
{

    /**
     * @param T $value
     * @return U
     * @throws ValidatorException
     */
    public function validate(mixed $value): mixed;
}
