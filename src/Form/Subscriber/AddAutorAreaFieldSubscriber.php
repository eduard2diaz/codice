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
class AddAutorAreaFieldSubscriber  implements EventSubscriberInterface{

    private $factory;
    private $token;
    private $authorizationChecker;
    private $areaService;

    public function __construct(FormFactoryInterface $factory,TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $areaService)
    {
        $this->factory = $factory;
        $this->token = $token;
        $this->authorizationChecker = $authorizationChecker;
        $this->areaService = $areaService;
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',

        );
    }

    /**
     * Cuando el usuario llene los datos del formulario y haga el envío del mismo,
     * este método será ejecutado.
     */
    public function preSubmit(FormEvent $event) {
        $data = $event->getData();
        if(null===$data){
            return;
        }

        $institucion= isset($data['institucion']) ? $data['institucion'] : $this->token->getToken()->getUser()->getInstitucion()->getId();
        $this->addElements($event->getForm(), $institucion);
    }

    protected function addElements($form, $institucion) {
        $form->add($this->factory->createNamed('area',EntityType::class,null,array(
            'auto_initialize'=>false,
            'class'         =>'App:Area',
            'choice_label' => function ($elemento) {
                return $elemento->getNombre();
            },
            'required'=>true,
            'label'=>'Área',
            'query_builder'=>function(EntityRepository $repository)use($institucion){
                $qb=$repository->createQueryBuilder('area')
                    ->innerJoin('area.institucion','p');
                if($institucion instanceof Institucion){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$institucion);
                }elseif(is_numeric($institucion)){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$institucion);
                }else{
                    $qb->where('p.id = :id')
                        ->setParameter('id',null);
                }
                return $qb;
            }
        )));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
       if(null==$data->getId()){
           if($this->authorizationChecker->isGranted('ROLE_SUPERADMIN'))
                $form->add('area',null,array('label'=>'Área','choices'=>array()));
           elseif($this->authorizationChecker->isGranted('ROLE_ADMIN'))
               $this->addElements($form,$this->token->getToken()->getUser()->getInstitucion()->getId());
           elseif($this->authorizationChecker->isGranted('ROLE_DIRECTIVO')){
               $area=$this->token->getToken()->getUser()->getArea();
               $areas=$this->areaService->areasHijas($area);
               $areas[]=$area;
               $form->add('area',null,array('required'=>true,'label'=>'Área','choices'=>$areas));
           }
        }else
       {
           if($data->getJefe()==null){
           $institucion= is_array($data) ? $data['institucion'] : $data->getInstitucion();
           $this->addElements($event->getForm(), $institucion);
           }else{
               $area=$data->getJefe()->getArea();
               $areas=$this->areaService->areasHijas($area);
               $areas[]=$area;
               $form->add('area',null,array('required'=>true,'label'=>'Área','choices'=>$areas));
           }


       }

    }





}
