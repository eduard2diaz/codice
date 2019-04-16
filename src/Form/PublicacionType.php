<?php

namespace App\Form;

use App\Entity\Publicacion;
use App\Form\Transformer\DatetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class PublicacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titulo', TextType::class, ['label'=>'Título','attr' => ['class' => 'form-control','autocomplete'=>'off']])
            ->add('resumen', TextareaType::class, ['attr' => ['class' => 'form-control']])
            ->add('keywords', TextType::class, ['label'=>'Palabras claves','attr' => ['class' => 'form-control','autocomplete'=>'off']])
            ->add('fechaCaptacion', TextType::class, array('label'=>'Fecha de publicación','attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('file', FileType::class, array('label'=>' ','required' => true,'attr'=>['style' => 'display:none',]))
        ;
        $builder->get('fechaCaptacion')
            ->addModelTransformer(new DatetoStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Publicacion::class,
        ]);
    }
}
