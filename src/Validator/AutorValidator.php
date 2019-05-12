<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\Persistence\ManagerRegistry;

class AutorValidator extends ConstraintValidator
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {

        /* @var $constraint App\Validator\Autor */
        $pa = PropertyAccess::createPropertyAccessor();

        if (!$constraint instanceof Autor) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Autor');
        }

        if ($constraint->em) {
            $em = $this->registry->getManager($constraint->em);
            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->em));
            }
        } else {
            $em = $this->registry->getManagerForClass(get_class($value));

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', get_class($value)));
            }
        }

        $id = $pa->getValue($value, 'id');

        if (null != $id) {
            $roles = $pa->getValue($value, $constraint->idrol);
            $esDirectivo = false;
            foreach ($roles->toArray() as $value)
                if ($value->getNombre() == 'ROLE_DIRECTIVO' || $value->getNombre()=='ROLE_ADMIN')
                    $esDirectivo = true;
            if ($esDirectivo == false) {
                $cadena = "SELECT COUNT(a) FROM App:Autor a JOIN a.jefe j WHERE j.id= :id";
                $consulta = $em->createQuery($cadena);
                $consulta->setParameter('id', $id);
                $result = $consulta->getResult();
                if ($result[0][1] > 0)
                    $this->context->buildViolation($constraint->message)->atPath($constraint->idrol)->addViolation();
            }
        }
    }
}
