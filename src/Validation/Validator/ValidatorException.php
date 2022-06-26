<?php

declare(strict_types=1);

namespace Empress\Validation\Validator;

final class ValidatorException extends \Exception
{
    public function __construct(string $message = '', private array $exceptions = [])
    {
        parent::__construct($message);
    }

    /**
     * @param ValidatorException[] $exceptions
     */
    public static function collect(array $exceptions): static
    {
        return new self('Validation errors', $exceptions);
    }

    /**
     * @return ValidatorException[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
