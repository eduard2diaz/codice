<?php

namespace App\Form\Subscriber;

use App\Entity\Institucion;
use App\Entity\Ministerio;
use App\Services\AreaService;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Description of AddCargoFieldSubscriber
 *
 * @author eduardo
 */
class AddAreaAreaPadreFieldSubscriber  implements EventSubscriberInterface{

    private $factory;
    private $areaService;

    public function __construct(FormFactoryInterface $factory,AreaService $areaService)
    {
        $this->factory = $factory;
        $this->areaService=$areaService;
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
        $institucion= is_array($data) ? $data['institucion'] : $data->getInstitucion();
        $this->addElements($event->getForm(), $institucion);
    }

    protected function addElements($form, $institucion,$areasHijas=null) {
        $form->add($this->factory->createNamed('padre',EntityType::class,null,array(
            'auto_initialize'=>false,
            'required'=>false,
            'label'=>'Área padre',
            'class'         =>'App:Area',
            'choice_label' => function ($elemento) {
                return $elemento->getNombre();
            },
            'query_builder'=>function(EntityRepository $repository)use($institucion,$areasHijas){
                $qb=$repository->createQueryBuilder('padre')
                    ->innerJoin('padre.institucion','p');
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
                if(null!=$areasHijas && count($areasHijas)>0)
                    $qb->andWhere('padre.id IN (:hijas)')->setParameter('hijas',$areasHijas);
                return $qb;
            }
        )));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        if(null==$data->getId()){
           $form->add('padre',null,array('label'=>'Área padre','required'=>false,'choices'=>array()));
        }else
       {
           $institucion= is_array($data) ? $data['institucion'] : $data->getInstitucion();
           $areasHijas=$this->areaService->areasNoHijas($data);
           $this->addElements($event->getForm(), $institucion,$areasHijas);
       }

    }





}
