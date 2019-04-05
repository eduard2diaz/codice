<?php

namespace App\Form;

use App\Entity\BalanceAnual;
use Symfony\Component\Form\AbstractType;
use App\Form\Transformer\DatetoStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BalanceAnualType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',TextType::class,['attr'=>['class'=>'form-control']])
            ->add('descripcion',TextareaType::class,['required'=>false,'label'=>'Descripción','attr'=>['class'=>'form-control']])

            ->add('fecha', TextType::class, array('label'=>'Fecha','attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $obj) {
            $form = $obj->getForm();
            $data = $obj->getData();
            if (null == $data->getId())
                $form->add('file', FileType::class, array('label'=>'Archivo','required' => true));
        });

        $builder->get('fecha')
            ->addModelTransformer(new DatetoStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BalanceAnual::class,
        ]);
    }
}
