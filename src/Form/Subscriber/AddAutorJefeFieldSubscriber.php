<?php

namespace App\Form\Subscriber;

use App\Entity\Institucion;
use App\Entity\Ministerio;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Services\AreaService;


/**
 * Description of AddCargoFieldSubscriber
 *
 * @author eduardo
 */
class AddAutorJefeFieldSubscriber implements EventSubscriberInterface
{

    private $factory;
    private $token;
    private $authorizationChecker;
    private $areaService;

    public function __construct(FormFactoryInterface $factory, TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $areaService)
    {
        $this->factory = $factory;
        $this->token = $token;
        $this->authorizationChecker = $authorizationChecker;
        $this->areaService = $areaService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',

        );
    }

    /**
     * Cuando el usuario llene los datos del formulario y haga el envío del mismo,
     * este método será ejecutado.
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (null === $data) {
            return;
        }
        $institucion= isset($data['institucion']) ? $data['institucion'] : $this->token->getToken()->getUser()->getInstitucion()->getId();
        $this->addElements($event->getForm(), $institucion);
    }

    protected function addElements($form, $institucion, $nosubodinados = null)
    {
        $form->add($this->factory->createNamed('jefe', EntityType::class, null, array(
            'auto_initialize' => false,
            'class' => 'App:Autor',
            'choice_label' => function ($elemento) {
                return $elemento->getNombre();
            },
            'required' => false,
            'query_builder' => function (EntityRepository $repository) use ($institucion, $nosubodinados) {
                $qb = $repository->createQueryBuilder('jefe')
                    ->innerJoin('jefe.institucion', 'p')
                    ->innerJoin('jefe.idrol', 'r')
                    ->where('r.nombre= "ROLE_DIRECTIVO" ');
                if ($institucion instanceof Institucion) {
                    $qb->where('p.id = :institucion')
                        ->setParameter('institucion', $institucion);
                } elseif (is_numeric($institucion)) {
                    $qb->where('p.id = :institucion')
                        ->setParameter('institucion', $institucion);
                } else {
                    $qb->where('p.id = :institucion')
                        ->setParameter('institucion', null);
                }

                if ($nosubodinados != null && count($nosubodinados) > 0) {
                        $qb->andWhere('jefe  IN (:id)')->setParameter('id', $nosubodinados);
                }
                return $qb;
            }
        )));
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if (null == $data->getId()) {
            if ($this->authorizationChecker->isGranted('ROLE_SUPERADMIN'))
                $form->add('jefe', null, array('required' => false, 'choices' => array()));
            else {
                $directivos = $this->areaService->obtenerDirectivos($this->token->getToken()->getUser()->getInstitucion()->getId());
                $form->add('jefe', null, array('required' => false, 'choices' => $directivos));
            }
        } else {
            $institucion = is_array($data) ? $data['institucion'] : $data->getInstitucion();
            $nosubordinados = $this->areaService->obtenerDirectivosNoSubordinados($data);
            if (empty($nosubordinados))
                $form->add('jefe', null, array('required' => false, 'choices' => array()));
            else
                $this->addElements($event->getForm(), $institucion, $nosubordinados);
        }

    }
}
