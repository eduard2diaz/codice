<?php

namespace App\Form;

use App\Entity\Autor;
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
use App\Entity\Area;
use App\Entity\Rol;
use Doctrine\ORM\EntityRepository;

class AutorType extends AbstractType
{
    private $token;
    private $authorizationChecker;
    private $areaService;

    public function __construct(TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $areaService)
    {
        $this->token = $token;
        $this->authorizationChecker = $authorizationChecker;
        $this->areaService = $areaService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      //  $esAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
      //  $area = $this->areaService->areasHijas($this->token->getToken()->getUser()->getArea(), $esAdmin);
      //  false = $options['data']->getId() == $this->token->getToken()->getUser()->getId();

        $disabled=false;
        $builder
            ->add('nombre',TextType::class,['attr'=>['class'=>'form-control m-input','autocomplete'=>'off']])
            ->add('usuario',TextType::class,['label'=>'Nombre de usuario','attr'=>['class'=>'form-control m-input','autocomplete'=>'off']])
            ->add('email',EmailType::class,['label'=>'Correo electrónico','attr'=>['class'=>'form-control m-input','autocomplete'=>'off']])
            ->add('phone',TextType::class,['label'=>'Teléfono','required'=>false,'attr'=>['class'=>'form-control m-input','autocomplete'=>'off']])
            ->add('gradoCientifico',null,['label'=>'Grado científico','required'=>true,'attr'=>['class'=>'form-control m-input']])
            ->add('activo', null, array('disabled' => $disabled, 'required' => false, 'attr' => array('data-on-text' => 'Si', 'data-off-text' => 'No')))

            ->add('area',null,[/*'choices' => $area, 'disabled' => false,*/ 'label'=>'Área','required'=>true,'attr'=>['class'=>'form-control m-input']])
            ->add('pais',null,['label'=>'País de residencia',/*'disabled' => false,*/])

            ->add('file', FileType::class, array('required' => false,
                'attr' => array('style' => 'display:none',
                    'accept' => 'image/*','accept' => '.jpg,.jpeg,.png,.gif,.bmp,.tiff')
            ))

        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $obj) {
            $form = $obj->getForm();
            $data = $obj->getData();
            $required = false;
            $constraint = array();
            if (null == $data->getId()) {
                $required = true;
                $constraint[] = new Assert\NotBlank();
            }

            $form->add('password', RepeatedType::class, array('required' => $required,
                'type' => PasswordType::class,
                'constraints' => $constraint,
                'invalid_message' => 'Ambas contraseñas deben coincidir',
                'first_options' => array('label' => 'Contraseña'
                , 'attr' => array('class' => 'form-control input-medium')),
                'second_options' => array('label' => 'Confirmar contraseña', 'attr' => array('class' => 'form-control input-medium'))
            ));
        });

        if (true == true) {
            $builder->add('idrol', null, array(/*'disabled' => false,*/ 'label' => 'Rol', 'required' => true, 'attr' => array('class' => 'form-control input-medium')));
            if (null == $options['data']->getId())
                $builder->add('jefe', null, array('choices' => $this->areaService->obtenerDirectivos(), 'label' => 'Jefe', 'placeholder' => 'Seleccione un directivo', 'required' => false, 'attr' => array('class' => 'form-control input-medium')));
            else {
                $subordinados = $this->areaService->subordinadosKey($options['data']);
                $id = $options['data']->getId();
                $builder->add('jefe', null, array(
                    'disabled' => false,
                    'required' => false,
                    'class' => Autor::class,
                    'query_builder' => function (EntityRepository $er) use ($subordinados, $id) {
                        $qb = $er->createQueryBuilder('u')
                            ->join('u.idrol', 'r')
                            ->where('r.nombre= :role AND u.id!= :id')
                            ->setParameters(['role' => 'ROLE_DIRECTIVO', 'id' => $id]);
                        if (!empty($subordinados)) {
                            $qb->andWhere('u.id NOT IN (:subordinados)')->setParameter('subordinados', $subordinados);
                        }
                        return $qb;
                    },
                    'placeholder' => 'Seleccione un directivo', 'attr' => array('class' => 'form-control input-medium')
                ));
            }

        } else
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

        $factory = $builder->getFormFactory();
        $builder->addEventSubscriber(new AddInstitucionMinisterioFieldSubscriber($factory));
        $builder->addEventSubscriber(new AddAutorInstitucionFieldSubscriber($factory));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Autor::class,
        ]);
    }
}
