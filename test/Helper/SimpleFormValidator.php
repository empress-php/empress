<?php

namespace Empress\Test\Helper;

use Amp\Http\Server\FormParser\Form;
use Empress\Validation\Registry\ValidatorRegistry;
use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;

class SimpleFormValidator implements ValidatorInterface
{
    public function __construct(private ValidatorRegistry $registry)
    {
    }

    public function validate(mixed $form): SimpleForm
    {
        if (!$form instanceof Form) {
            throw new ValidatorException();
        }

        $field1 = $this->registry
            ->contextFor($form->getValue('field1'))
            ->to('int')
            ->unwrap();

        return new SimpleForm($field1);
    }
}
