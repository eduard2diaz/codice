<?php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use App\Tool\FileStorageManager;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UsuarioSubscriber implements EventSubscriber
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
        if ($entity instanceof Usuario){
            $entity->setPassword($this->getServiceContainer()->get('security.password_encoder')->encodePassword($entity,$entity->getPassword()));
            $ruta=$this->getServiceContainer()->getParameter('storage_directory');
            $file=$entity->getFile();
            $nombreArchivoFoto=FileStorageManager::Upload($ruta,$file);
            if (null!=$nombreArchivoFoto){
                $entity->setRutaFoto($nombreArchivoFoto);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Usuario && null!=$entity->getRutaFoto()) {
            $directory = $this->getServiceContainer()->getParameter('storage_directory');
            $ruta=$directory . DIRECTORY_SEPARATOR . $entity->getRutaFoto();
            FileStorageManager::removeUpload($ruta);
        }
    }


    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preRemove',
        ];
    }
}
