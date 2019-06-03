<?php

namespace App\Form;

use App\Entity\TipoArticulo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TipoArticuloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class,['attr'=>['autocomplete'=>'off','class'=>'form-control input-xlarge']])
            ->add('grupo',null,['required'=>true,'attr'=>['autocomplete'=>'off','class'=>'form-control input-xlarge']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TipoArticulo::class,
        ]);
    }
}
