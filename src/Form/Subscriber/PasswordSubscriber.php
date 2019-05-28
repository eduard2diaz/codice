<?php


namespace App\Form\Subscriber;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PasswordSubscriber implements EventSubscriberInterface
{

    public static function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $required = false;
        $constraint = array();
        if (null == $data->getId()) {
            $required = true;
            $constraint[] = new Assert\NotBlank();
        }

        $form->add('password', RepeatedType::class, array('required' => false,
            'type' => PasswordType::class,
            'constraints' => $constraint,
            'invalid_message' => 'Ambas contraseñas deben coincidir',
            'first_options' => array('label' => 'Contraseña'
            , 'attr' => array('class' => 'form-control input-medium')),
            'second_options' => array('label' => 'Confirmar contraseña', 'attr' => array('class' => 'form-control input-medium'))
        ));
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',

        );
    }
}