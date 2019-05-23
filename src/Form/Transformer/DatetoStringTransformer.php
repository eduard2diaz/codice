<?php

namespace App\Form\Transformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DatetoStringTransformer implements DataTransformerInterface
{
    /*
     *Antes de dibujar el formulario, convierte su informacion a string utilizando el metodo format e indicando el formato
     * en que se quiere representar la fecha
     */
    public function transform($datetime)
    {
        if (null === $datetime) {
            return;
        }
        return $datetime->format('Y-m-d');
    }

    /**
     *Antes de enviar el formulario, parsea la fecha, valida que la fecha sea valida y en caso contrario lanza una excepción
     *indicando que la fecha no es valida
     */

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
