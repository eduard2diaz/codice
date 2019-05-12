<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\Persistence\ManagerRegistry;

class PaisValidator extends ConstraintValidator
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {

        /* @var $constraint App\Validator\Pais */
        $pa = PropertyAccess::createPropertyAccessor();

        if (!$constraint instanceof Pais) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Pais');
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
        $codigo = $pa->getValue($value, $constraint->codigo);
        $codigos = explode(',', $codigo);

        foreach ($codigos as $cod) {
            if (null != $id) {
                $consulta = $em->createQuery('SELECT COUNT(p.id) FROM App:Pais p WHERE p.codigo like :parametro AND p.id!= :id');
                $consulta->setParameters(['parametro' => '%' . trim($cod) . '%', 'id' => $id]);
                $result = $consulta->getResult();
            } else {
                $consulta = $em->createQuery('SELECT COUNT(p.id) FROM App:Pais p WHERE p.codigo like :parametro AND p.id!= :id');
                $consulta->setParameters(['parametro' => '%' . trim($cod) . '%', 'id' => $id]);
                $result = $consulta->getResult();
            }
            if ($result[0][1] > 0) {
                $this->context->buildViolation($constraint->message)->setParameter('%codigo%',$cod)->atPath($constraint->codigo)->addViolation();
                break;
            }

        }
    }
}
