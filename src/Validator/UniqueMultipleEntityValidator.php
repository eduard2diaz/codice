<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\Persistence\ManagerRegistry;

class UniqueMultipleEntityValidator extends ConstraintValidator
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {

        /* @var $constraint App\Validator\UniqueMultipleEntity */
        $pa = PropertyAccess::createPropertyAccessor();

        if (!$constraint instanceof UniqueMultipleEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueMultipleEntity');
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

        /*
         *Este validador funciona similar a CentrocostoValidator con la diferencia que obtenemos la institución a traves
         * del atributo ccosto(centrocosto) que posse la entidad área y luego de tener el centro de costo obtenermos la
         * institucion a través de un atributo cuenta que posee la clase centro de costo y nuevamente a partir de dicho
         * atributo obtenemos la institución.
         */
        $class = $em->getClassMetadata(get_class($value));
        $repository = $em->getRepository(get_class($value));

        $field = $constraint->field;
        $valor = $pa->getValue($value, $constraint->field);
        $entities = $constraint->entities;
        $id = $pa->getValue($value, 'id');
        $entity = $repository->getClassName();


        foreach ($entities as $value) {
            $parameters=['valor'=>$valor];
            if (!$id) {
                $cadena = "SELECT COUNT(a) FROM App:$value a WHERE a.$field= :valor";
            } else {
                $cadena = "SELECT COUNT(a) FROM App:$value a WHERE a.$field= :valor AND a.id!= :id";
                $parameters['id'] = $id;
            }

            $consulta = $em->createQuery($cadena);
            $consulta->setParameters($parameters);
            $result = $consulta->getResult();
            if ($result[0][1] > 0){
                $this->context->buildViolation($constraint->message)->setParameter('{{ value }}',$valor)->atPath($field)->addViolation();
                break;
            }
        }


    }
}
