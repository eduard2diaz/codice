<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UsuarioType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = $this->token->getToken()->getUser()->getId() == $options['data']->getId();
        $builder
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off','pattern'=>'[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{2,})*$']])
            ->add('usuario', TextType::class, ['label' => 'Nombre de usuario', 'attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off','pattern'=>'([a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3})*$']])
            ->add('email', EmailType::class, ['label' => 'Correo electrónico', 'attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off']])
            ->add('file', FileType::class, array('required' => false,
                'attr' => array('style' => 'display:none',
                    'accept' => 'image/*',/*'accept' => '.jpg,.jpeg,.png,.gif,.bmp,.tiff'*/)
            ));

        if ($this->token->getToken()->getUser()->getId() != $options['data']->getId())
            $builder->add('activo', null, array('disabled' => $disabled, 'required' => false, 'attr' => array('data-on-text' => 'Si', 'data-off-text' => 'No')));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $obj) {
            $form = $obj->getForm();
            $data = $obj->getData();
            $required = false;
            $constraint = array();
            if (null == $data->getId()) {
                $required = true;
                $constraint[] = new Assert\NotBlank();
            }

            $form->add('password', RepeatedType::class, array('required' => false,
                'type' => PasswordType::class,
                'constraints' => $constraint,
                'invalid_message' => 'Ambas contraseñas deben coincidir',
                'first_options' => array('label' => 'Contraseña'
                , 'attr' => array('class' => 'form-control input-medium')),
                'second_options' => array('label' => 'Confirmar contraseña', 'attr' => array('class' => 'form-control input-medium'))
            ));
        });;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
