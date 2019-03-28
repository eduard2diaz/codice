<?php

namespace App\Form;

use App\Entity\Premio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PremioType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',PublicacionType::class)
            ->add('institucionConcede',null,['label'=>'Institución que lo concede','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('tipoPremio',null,['label'=>'Tipo de premio','required'=>true,'attr'=>['class'=>'form-control']]);

        if($this->token->getToken()->getUser()->getId()!=$options['data']->getId()->getAutor()->getId())
            $builder->get('id')->add('estado',ChoiceType::class,['choices'=>[
                'Pendiente a aprobación'=>0,
                'Publicación aprobada'=>1,
                'Publicación rechazada'=>2,
            ]]);

        if($options['data']->getId()->getId()!=null)
            $builder->get('id')->remove('file');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Premio::class,
        ]);
    }
}
