<?php

namespace App\EventSubscriber;

use App\Entity\Autor;
use App\Entity\Publicacion;
use App\Entity\Articulo;
use App\Entity\Encuentro;
use App\Entity\Libro;
use App\Entity\Monografia;
use App\Entity\Norma;
use App\Entity\Patente;
use App\Entity\Premio;
use App\Entity\Software;
use App\Entity\Tesis;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;

class PublicacionSubscriber implements EventSubscriber
{
    private $serviceContainer;

    function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return mixed
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    public function prePersist(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Publicacion) {
            $entity->Upload($this->getServiceContainer()->getParameter('storage_directory'));
            $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');

            if ($entity->getAutor()->getJefe() == null)
                $entity->setEstado(1);

            if ($entity->getEstado() == 1) {
                foreach ($entity->getAutor()->getSeguidores() as $seguidor) {
                    $notificacionService->nuevaNotificacion($seguidor->getId(), $entity->getAutor()->getNombre() . ' ha publicado "' . $entity->getTitulo() . '"');
                }
            }

            $currentUser = $this->getServiceContainer()->get('security.token_storage')->getToken()->getUser();
            if ($currentUser->getId() == $entity->getAutor()->getId()) {
                if ($entity->getAutor()->getJefe() != null)
                    $notificacionService->nuevaNotificacion($entity->getAutor()->getJefe()->getId(), "El usuario " . $currentUser->__toString() . " publicó su encuentro " . $entity->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($entity->getAutor()->getId(), "El usuario " . $currentUser->__toString() . " ha publicado tu encuentro " . $entity->getTitulo());

        }

    }

    public function preUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Publicacion) {
            $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');

            if ($entity->getAutor()->getJefe() == null)
                $entity->setEstado(1);

            if ($entity->getEstado() == 1) {
                foreach ($entity->getAutor()->getSeguidores() as $seguidor) {
                    $notificacionService->nuevaNotificacion($seguidor->getId(), $entity->getAutor()->getNombre() . ' ha actualizado su publicación "' . $entity->getTitulo() . '"');
                }
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Publicacion) {
            $fs = new Filesystem();
            $directory = $this->getServiceContainer()->getParameter('storage_directory');
            $fs->remove($directory . DIRECTORY_SEPARATOR . $entity->getRutaArchivo());

            $currentUser = $this->getServiceContainer()->get('security.token_storage')->getToken()->getUser();
            $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');

            if ($currentUser->getId() == $entity->getAutor()->getId()) {
                if ($entity->getAutor()->getJefe() != null)
                    $notificacionService->nuevaNotificacion($entity->getAutor()->getJefe()->getId(), "La usuario " . $currentUser->__toString() . " eliminó su articulo " . $entity->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($entity->getAutor()->getId(), "El usuario " . $currentUser->__toString() . " ha eliminado tu articulo " . $entity->getTitulo());


        }
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
            'preRemove',
        ];
    }
}
