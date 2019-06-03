<?php

namespace App\Form;

use App\Entity\Libro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LibroType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('volumen',TextType::class,['required'=>true,'attr'=>['class'=>'form-control']])
            ->add('numero',TextType::class,['label'=>'Número','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('serie',TextType::class,['required'=>true,'attr'=>['class'=>'form-control']])
            ->add('paginas',IntegerType::class,['label'=>'Páginas','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('isbn',TextType::class,['label'=>'ISBN','required'=>true,'attr'=>['class'=>'form-control']])
            ->add('id',PublicacionType::class)
            ->add('editorial',null,['required'=>true,'attr'=>['class'=>'form-control']])
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
            'data_class' => Libro::class,
        ]);
    }
}
