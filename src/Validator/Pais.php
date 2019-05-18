<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Pais extends Constraint
{
    public $message = 'Ya existe un país con el codigo %codigo%';
    public $service = 'pais.validator';
    public $em = null;
    public $codigo;
    public $repositoryMethod = 'findBy';
    public $errorPath = 'codifo';
    public $ignoreNull = true;

    public function getRequiredOptions()
    {
        return ['codigo'];
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
        return 'codigo';
    }
}
