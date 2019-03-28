<?php

namespace App\Form;

use App\Entity\Editorial;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EditorialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class,array('attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('pais',null,['label'=>'País','required'=>true])
            ->add('direccion', TextareaType::class,array('label'=>'Dirección','attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('correo', EmailType::class,array('label'=>'Correo electrónico','required'=>true,'attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('telefono', TextType::class,array('label'=>'Teléfono','required'=>false,'attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Editorial::class,
        ]);
    }
}
