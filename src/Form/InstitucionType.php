<?php

namespace App\Form;

use App\Entity\Institucion;
use App\Form\Subscriber\AddInstitucionMinisterioFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InstitucionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class,['attr'=>['autocomplete'=>'off','class'=>'form-control input-xlarge']])
            ->add('pais',null,['label'=>'País'])
        ;

        /*
         * Cargo el AddInstitucionMinisterioFieldSubscriber que permite listar los ministerios por pais
         */
        $factory = $builder->getFormFactory();
        $builder->addEventSubscriber(new AddInstitucionMinisterioFieldSubscriber($factory));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Institucion::class,
        ]);
    }
}
