<?php

namespace App\Form;

use App\Entity\Evento;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EventoType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isbn',TextType::class,['label'=>'ISBN','required'=>true,'attr'=>['class'=>'form-control','autocomplete'=>'off']])
            ->add('ciudad',TextType::class,['required'=>true,'attr'=>['class'=>'form-control','autocomplete'=>'off']])
            ->add('issn',TextType::class,['label'=>'ISSN','required'=>true,'attr'=>['class'=>'form-control','autocomplete'=>'off']])
            ->add('id',PublicacionType::class)
            ->add('tipoEvento',null,['label'=>'Tipo de evento','required'=>true])
            ->add('organizador',null,['required'=>true])
            ->add('pais', null, ['label'=>'País','required' => true, 'attr' => ['class' => 'form-control']])// ->add('idautor')
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
            'data_class' => Evento::class,
        ]);
    }
}
