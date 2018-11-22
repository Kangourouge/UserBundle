<?php

namespace KRG\UserBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailUnique extends Constraint
{
    public $message = 'Email "{{ string }}" is already used, please choose another one.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
