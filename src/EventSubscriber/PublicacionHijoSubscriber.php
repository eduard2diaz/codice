<?php

namespace App\EventSubscriber;

use App\Entity\Articulo;
use App\Entity\Autor;
use App\Entity\Encuentro;
use App\Entity\Libro;
use App\Entity\Monografia;
use App\Entity\Norma;
use App\Entity\Patente;
use App\Entity\Premio;
use App\Entity\Publicacion;
use App\Entity\Software;
use App\Entity\Tesis;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PublicacionHijoSubscriber implements EventSubscriber
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
        if (($entity instanceof Libro) ||
            ($entity instanceof Tesis) ||
            ($entity instanceof Software)||
            ($entity instanceof Premio)||
            ($entity instanceof Patente)||
            ($entity instanceof Norma)||
            ($entity instanceof Monografia)||
            ($entity instanceof Encuentro)||
            ($entity instanceof Articulo)){
            $entity->getId()->setChildType(get_class($entity));
            $em->persist($entity->getId());
            $em->flush($entity->getId());
        }

    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }
}
