<?php

namespace App\Form;

use App\Entity\Autor;
use App\Form\Subscriber\AddAutorAreaFieldSubscriber;
use App\Form\Subscriber\AddAutorJefeFieldSubscriber;
use App\Form\Subscriber\PasswordSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\Subscriber\AddInstitucionMinisterioFieldSubscriber;
use App\Form\Subscriber\AddAutorInstitucionFieldSubscriber;
use App\Services\AreaService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Rol;
use Doctrine\ORM\EntityRepository;

class AutorType extends AbstractType
{
    private $token;
    private $authorizationChecker;
    private $areaService;

    public function __construct(TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $areaService)
    {
        //Obtengo una referencia del actual usuario
        $this->token = $token;
        //Obtengo un mecanismo de acceso a los reloes de usuairo actual
        $this->authorizationChecker = $authorizationChecker;
        //LLamo a una instancia del servicio AreaService
        $this->areaService = $areaService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $esAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
        $esSuperAdmin = $this->authorizationChecker->isGranted('ROLE_SUPERADMIN');
        $esDirectivo = $this->authorizationChecker->isGranted('ROLE_DIRECTIVO');
        $disabled = false;
        $builder
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off', 'pattern' => '[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{2,})*$']])
            ->add('usuario', TextType::class, ['label' => 'Nombre de usuario', 'attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off', 'pattern' => '([a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3})*$']])
            ->add('email', EmailType::class, ['label' => 'Correo electrónico', 'attr' => ['class' => 'form-control m-input', 'autocomplete' => 'off']])
            ->add('phone', TextType::class, ['label' => 'Teléfono', 'required' => false, 'attr' => ['pattern' => '((\+|\-)\d+)', 'placeholder' => 'Ej: +5347815142', 'class' => 'form-control m-input', 'autocomplete' => 'off']])
            ->add('gradoCientifico', null, ['label' => 'Grado científico', 'required' => true, 'attr' => ['class' => 'form-control m-input']])
            ->add('file', FileType::class, array('required' => false,
                'attr' => array('style' => 'display:none',
                    'accept' => 'image/*',/* 'accept' => '.jpg,.jpeg,.png,.gif,.bmp,.tiff'*/)
            ));

        if ($this->token->getToken()->getUser() != $options['data'])
            $builder->add('activo', null, array('disabled' => $disabled, 'required' => false, 'attr' => array('data-on-text' => 'Si', 'data-off-text' => 'No')));

        $builder->addEventSubscriber(new PasswordSubscriber());
        $factory = $builder->getFormFactory();

        /*
         * Chequeo de los roles del usuario actual y en caso de que sea un superadmin o un administrador institucional
         * se pueden asignar todos los permisos, en caso contrario solo podran ser asginados los permisos de directivo y trabajador
         */
        if (($esSuperAdmin || $esAdmin) && $this->token->getToken()->getUser() != $options['data']) {
            $builder->add('idrol', null, array('disabled' => false,
                'label' => 'Permisos', 'required' => true, 'attr' => array('class' => 'form-control input-medium')));

            /*
             * Si el usuario actual es un superadmin, creo los subscribers de las entidades cuyos datos fueron cargados por ajax
             */
            if ($esSuperAdmin) {
                $builder->add('pais', null, ['label' => 'País de residencia', 'disabled' => false,]);
                $builder->addEventSubscriber(new AddInstitucionMinisterioFieldSubscriber($factory));
                $builder->addEventSubscriber(new AddAutorInstitucionFieldSubscriber($factory));
            }

            $builder->addEventSubscriber(new AddAutorJefeFieldSubscriber($factory, $this->token, $this->authorizationChecker, $this->areaService));
        } elseif (($esDirectivo) && $this->token->getToken()->getUser() != $options['data']) {
            $builder->add('idrol', null, array(
                'disabled' => false,
                'class' => Rol::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.nombre IN (:roles)')
                        ->setParameter('roles', ['ROLE_DIRECTIVO', 'ROLE_USER']);
                },
                'label' => 'Permisos', 'disabled' => false, 'required' => true, 'attr' => array('class' => 'form-control input-medium')
            ));
        }

        if (($esSuperAdmin || $esAdmin || $esDirectivo) && $this->token->getToken()->getUser() != $options['data'])
            $builder->addEventSubscriber(new AddAutorAreaFieldSubscriber($factory, $this->token, $this->authorizationChecker, $this->areaService));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Autor::class,
        ]);
    }
}
