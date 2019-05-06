<?php

namespace App\EventSubscriber;

use App\Entity\Autor;
use App\Entity\Notificacion;
use App\Entity\Publicacion;
use App\Services\FileStorageManager;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

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
        if ($entity instanceof Publicacion) {
            $ruta=$this->getServiceContainer()->getParameter('storage_directory');
            $file=$entity->getFile();
            $nombreArchivoFoto=FileStorageManager::Upload($ruta,$file);
            if (null!=$nombreArchivoFoto)
                $entity->setRutaArchivo($nombreArchivoFoto);

            if ($entity->getAutor()->getJefe() == null)
                $entity->setEstado(1);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Publicacion) {
            $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');
            $seguidores=$this->obtenerSeguidores($entity->getAutor()->getId());

            if ($entity->getEstado() == 1 && count($seguidores)>0) {
                $descripcion=$entity->getAutor()->getNombre() . ' ha publicado "' . $entity->getTitulo() . '"';
                $fecha=new \DateTime();
                foreach ($seguidores as $seguidor) {
                    if($entity->getAutor()->getJefe() != null && $seguidor['id']==$entity->getAutor()->getJefe()->getId())
                        continue;

                    $seguidorObj=$em->getRepository(Autor::class)->find($seguidor['id']);
                    if(!$seguidorObj)
                        continue;
                    $notificacionService->nuevaNotificacion($seguidorObj->getId(), $descripcion);
                }
                $em->flush();
            }

            $currentUser = $this->getServiceContainer()->get('security.token_storage')->getToken()->getUser();
            if ($currentUser->getId() == $entity->getAutor()->getId()) {
                if ($entity->getAutor()->getJefe() != null)
                    $notificacionService->nuevaNotificacion($entity->getAutor()->getJefe()->getId(), "El usuario " . $currentUser->__toString() . " publicó " . $entity->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($entity->getAutor()->getId(), "El usuario " . $currentUser->__toString() . " ha registrado tu publicación " . $entity->getTitulo());
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Publicacion && $entity->getAutor()->getJefe() == null)
                $entity->setEstado(1);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');
        if ($entity instanceof Publicacion) {
                $seguidores=$this->obtenerSeguidores($entity->getAutor()->getId());

            if ($entity->getEstado() == 1 && count($seguidores)>0) {
                $descripcion=$entity->getAutor()->getNombre() . ' ha actualizado la publicación "' . $entity->getTitulo() . '"';
                $fecha=new \DateTime();
                foreach ($seguidores as $seguidor) {
                    if($entity->getAutor()->getJefe() != null && $seguidor['id']==$entity->getAutor()->getJefe()->getId())
                        continue;

                    $seguidorObj=$em->getRepository(Autor::class)->find($seguidor['id']);
                    if(!$seguidorObj)
                        continue;
                    $notificacionService->nuevaNotificacion($seguidorObj->getId(), $descripcion);
                }
                $em->flush();
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Publicacion) {
            $directory = $this->getServiceContainer()->getParameter('storage_directory');
            $ruta=$directory . DIRECTORY_SEPARATOR . $entity->getRutaArchivo();
            FileStorageManager::removeUpload($ruta);

            $currentUser = $this->getServiceContainer()->get('security.token_storage')->getToken()->getUser();
            $notificacionService = $this->getServiceContainer()->get('app.notificacion_service');

            if ($currentUser->getId() == $entity->getAutor()->getId()) {
                if ($entity->getAutor()->getJefe() != null)
                    $notificacionService->nuevaNotificacion($entity->getAutor()->getJefe()->getId(), "El usuario " . $currentUser->__toString() . " eliminó su publicación " . $entity->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($entity->getAutor()->getId(), "El usuario " . $currentUser->__toString() . " ha eliminado tu publicación " . $entity->getTitulo());


        }
    }

    private function obtenerSeguidores($id){
        $em=$this->getServiceContainer()->get('doctrine')->getManager();
        $consulta=$em->createQuery('SELECT s.id FROM App:Autor a JOIN a.seguidores s WHERE a.id= :id');
        $consulta->setParameter('id',$id);
        return $consulta->getResult();
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'postPersist',
            'preUpdate',
            'postUpdate',
            'preRemove',
        ];
    }
}
