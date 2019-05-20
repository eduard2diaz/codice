<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Validator\Pais as PaisConstrains;
use App\Entity\Pais;

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

        if (!$constraint instanceof PaisConstrains) {
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
        $paises=$em->getRepository(Pais::class)->findAll();
        $len=strlen($codigo);
        foreach ($paises as $pais){
            $len2=strlen($pais->getCodigo());
            $len2= $len2< $len ? $len2 : $len;
            if ($pais->getId() != $id && substr($codigo,0,$len2)==substr($pais->getCodigo(),0,$len2)){
                $this->context->buildViolation($constraint->message)->setParameter('%codigo%', $codigo)->atPath($constraint->codigo)->addViolation();
                break;
            }
        }

    }
}
