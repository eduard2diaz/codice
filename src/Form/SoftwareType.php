<?php

namespace App\Form;

use App\Entity\Software;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SoftwareType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numero',TextType::class,['required'=>true,'label'=>'Número','attr'=>['class'=>'form-control']])
            ->add('idioma',null,['required'=>true])
            ->add('id',PublicacionType::class)
            ->add('tipoSoftware',null,['required'=>true,'label'=>'Tipo de software','attr'=>['class'=>'form-control']])
        ;

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
            'data_class' => Software::class,
        ]);
    }
}
