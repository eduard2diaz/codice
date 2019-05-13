<?php

namespace App\Form\Transformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DatetoStringTransformer implements DataTransformerInterface
{
    public function transform($datetime)
    {

        if (null === $datetime) {
            return;
        }

        return $datetime->format('Y-m-d');
    }

    public function reverseTransform($issueNumber)
    {
        if (!$issueNumber) {
            return;
        }

        trim($issueNumber);
        $trozos = explode ("-", $issueNumber);
        $año=$trozos[0];
        $mes=$trozos[1];
        $dia=$trozos[2];
        if(checkdate ($mes,$dia,$año)){
            return new \DateTime($issueNumber);
        }

        throw new TransformationFailedException(sprintf('La fecha %s no es una fecha válida',$issueNumber));


    }

}
