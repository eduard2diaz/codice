<?php

namespace App\Form\Subscriber;

use App\Entity\Ministerio;
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
class AddAreaInstitucionFieldSubscriber  implements EventSubscriberInterface{

    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $ministerio= is_array($data) ? $data['ministerio'] : $data->getMinisterio();
        $this->addElements($event->getForm(), $ministerio);
    }

    protected function addElements($form, $ministerio) {
        $form->add($this->factory->createNamed('institucion',EntityType::class,null,array(
            'auto_initialize'=>false,
            'label'=>'Institución',
            'class'         =>'App:Institucion',
            'choice_label' => function ($elemento) {
                return $elemento->getNombre();
            },
            'query_builder'=>function(EntityRepository $repository)use($ministerio){
                $qb=$repository->createQueryBuilder('institucion')
                    ->innerJoin('institucion.ministerio','p');
                if($ministerio instanceof Ministerio){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$ministerio);
                }elseif(is_numeric($ministerio)){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$ministerio);
                }else{
                    $qb->where('p.id = :id')
                        ->setParameter('id',null);
                }
                return $qb;
            }
        )));
    }

    /**
     *Antes de dibujarlo en la vista
     */
    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
       if(null==$data->getId()){
           $form->add('institucion',null,array('label'=>'Institución','required'=>true,'choices'=>array()));
        }else
       {
           $ministerio= is_array($data) ? $data['ministerio'] : $data->getMinisterio();
           $this->addElements($event->getForm(), $ministerio);
       }

    }





}
