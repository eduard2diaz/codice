<?php

namespace App\Form\Subscriber;

use App\Entity\Pais;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Entity\Provincia;
use App\Entity\Municipio;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Description of AddCargoFieldSubscriber
 *
 * @author eduardo
 */
class AddInstitucionMinisterioFieldSubscriber implements EventSubscriberInterface
{

    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $data = $event->getData();
        if (null === $data) {
            return;
        }
        /*
         * Busco el pais que usuario selecciono y a partir del mismo busco los ministerios que pertenecen a dicho
         * pais
         */
        $pais = is_array($data) ? $data['pais'] : $data->getPais();
        $this->addElements($event->getForm(), $pais);
    }

    /*
     * Funcionalidad que lista los municipios que pertenecen a un determinado pais y con esto rellena dicho campo en el
     * formulario
     */
    protected function addElements($form, $pais)
    {
        $form->add($this->factory->createNamed('ministerio', EntityType::class, null, array(
            'auto_initialize' => false,
            'class' => 'App:Ministerio',
            'choice_label' => function ($elemento) {
                return $elemento->getNombre();
            },
            'query_builder' => function (EntityRepository $repository) use ($pais) {
                $qb = $repository->createQueryBuilder('ministerio')
                    ->innerJoin('ministerio.pais', 'p');
                if ($pais instanceof Pais) {
                    $qb->where('p.id = :id')
                        ->setParameter('id', $pais);
                } elseif (is_numeric($pais)) {
                    $qb->where('p.id = :id')
                        ->setParameter('id', $pais);
                } else {
                    $qb->where('p.id = :id')
                        ->setParameter('id', null);
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
            /*
             * Si estoy registrando una institucion nueva el listado de ministerios aparece en blanco
             */
            $form->add('ministerio', null, array('required' => true, 'choices' => array()));
        } else {
         /*
          * Busco el pais que usuario selecciono y a partir del mismo busco los ministerios que pertenecen a dicho
          * pais
          */
            $pais = is_array($data) ? $data['pais'] : $data->getPais();
            $this->addElements($event->getForm(), $pais);
        }

    }


}
