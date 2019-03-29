<?php

namespace App\EventSubscriber;

use App\Entity\Autor;
use App\Entity\Publicacion;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PublicacionSubscriber implements EventSubscriber
{
    private $serviceContainer;

    function __construct(ContainerInterface $serviceContainer) {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return mixed
     */
    public function getServiceContainer() {
        return $this->serviceContainer;
    }

    public function prePersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();
        $em=$args->getEntityManager();
        if ($entity instanceof Publicacion){
            $entity->Upload($this->getServiceContainer()->getParameter('storage_directory'));
            $notificacionService=$this->getServiceContainer()->get('app.notificacion_service');
            if($entity->getEstado()==1){
                foreach ($entity->getAutor()->getSeguidores() as $seguidor){
                    $notificacionService->nuevaNotificacion($seguidor->getId(),$entity->getAutor()->getNombre().' ha publicado "'.$entity->getTitulo().'"');
                }
            }



        }

    }

    public function preUpdate(LifecycleEventArgs $args) {

        $entity = $args->getEntity();
        $em=$args->getEntityManager();
        if ($entity instanceof Publicacion){
            $notificacionService=$this->getServiceContainer()->get('app.notificacion_service');
            if($entity->getEstado()==1){
                foreach ($entity->getAutor()->getSeguidores() as $seguidor){
                    $notificacionService->nuevaNotificacion($seguidor->getId(),$entity->getAutor()->getNombre().' ha actualizado su publicación "'.$entity->getTitulo().'"');
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }
}
