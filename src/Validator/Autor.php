<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Autor extends Constraint
{
    public $message = 'Para poder actualizar este usuario debe quitarle sus subordinados.';
    public $service = 'autor.validator';
    public $em = null;
    public $idrol;
    public $repositoryMethod = 'findBy';
    public $errorPath = 'idrol';
    public $ignoreNull = true;

    public function getRequiredOptions()
    {
        return ['idrol'];
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
        return 'idrol';
    }
}
