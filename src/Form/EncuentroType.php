<?php

namespace App\Form;

use App\Entity\Encuentro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EncuentroType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',TextType::class,['required'=>true,'attr'=>['class'=>'form-control']])
            ->add('isbn',TextType::class,['label'=>'ISBN','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('ciudad',TextType::class,['required'=>true,'attr'=>['class'=>'form-control']])
            ->add('issn',TextType::class,['label'=>'ISSN','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('id',PublicacionType::class)
            ->add('tipoEncuentro',null,['label'=>'Tipo de encuentro','required'=>true])
            ->add('organizador',null,['required'=>true])
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
            'data_class' => Encuentro::class,
        ]);
    }
}
