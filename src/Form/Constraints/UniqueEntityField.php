<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueEntityField extends Constraint
{
    public string $message = 'The "{{ value }}" value of "{{ fieldName }}" field must be unique';

    public string $fieldName;

    public string $entityName;

    public ?object $entityInstance = null;
}
