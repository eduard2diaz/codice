<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Institucion;
use App\Form\Subscriber\AddAreaAreaPadreFieldSubscriber;
use App\Form\Subscriber\AddAreaInstitucionFieldSubscriber;
use App\Form\Subscriber\AddInstitucionMinisterioFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Services\AreaService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityRepository;

class AreaType extends AbstractType
{
    private $token;
    private $authorizationChecker;
    private $area_service;

    public function __construct(TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $area_service)
    {
        $this->token = $token;
        $this->authorizationChecker = $authorizationChecker;
        $this->area_service = $area_service;
    }

    /**
     * @return AreaService
     */
    public function getAreaService(): AreaService
    {
        return $this->area_service;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $area = $options['data'];
        $builder
            ->add('nombre', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge', 'pattern'=>'[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{1,})*$')));

        if ($this->authorizationChecker->isGranted('ROLE_SUPERADMIN')) {
            $builder->add('pais', null, ['label' => 'País']);
            $factory = $builder->getFormFactory();
            $builder->addEventSubscriber(new AddInstitucionMinisterioFieldSubscriber($factory));
            $builder->addEventSubscriber(new AddAreaInstitucionFieldSubscriber($factory));
            $builder->addEventSubscriber(new AddAreaAreaPadreFieldSubscriber($factory, $this->area_service));
        } else {
            if (null == $area->getId()) {
                $institucion=$this->token->getToken()->getUser()->getInstitucion()->getId();
                $builder->add('padre', EntityType::class, array('label' => 'Área padre',
                    'class' => 'App:Area',
                    'required'=>false,
                    'query_builder' => function (EntityRepository $repository) use ($institucion) {
                        $qb = $repository->createQueryBuilder('padre')
                            ->innerJoin('padre.institucion', 'p');
                        if ($institucion instanceof Institucion) {
                            $qb->where('p.id = :id')
                                ->setParameter('id', $institucion);
                        } elseif (is_numeric($institucion)) {
                            $qb->where('p.id = :id')
                                ->setParameter('id', $institucion);
                        } else {
                            $qb->where('p.id = :id')
                                ->setParameter('id', null);
                        }
                        return $qb;
                    }

                , 'attr' => array('class' => 'form-control input-medium')));
            } else
                $builder->add('padre', null, array('label' => 'Área padre', 'choices' => $this->getAreaService()->areasNoHijas($area),
                    'attr' => array('class' => 'form-control input-medium')));
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }
}
