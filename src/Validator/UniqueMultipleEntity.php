<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueMultipleEntity extends Constraint
{
    public $message = 'El valor "{{ value }}" ya está en uso.';
    public $service = 'uniquemultipleentity.validator';
    public $em = null;
    public $repositoryMethod = 'findBy';
    public $field;
    public $entities;
    public $errorPath = 'usuario';
    public $ignoreNull = true;

    public function getRequiredOptions()
    {
        return ['field','entities'];
    }

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption()
    {
        return 'field';
    }
}
