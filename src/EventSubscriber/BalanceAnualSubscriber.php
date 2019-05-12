<?php

namespace App\EventSubscriber;

use App\Entity\BalanceAnual;
use App\Tool\FileStorageManager;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class BalanceAnualSubscriber implements EventSubscriber
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
        if ($entity instanceof BalanceAnual) {
            $ruta = $this->getServiceContainer()->getParameter('storage_directory');
            $file = $entity->getFile();
            $nombreArchivoFoto = FileStorageManager::Upload($ruta, $file);
            if (null != $nombreArchivoFoto)
                $entity->setRutaArchivo($nombreArchivoFoto);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof BalanceAnual) {
            $directory = $this->getServiceContainer()->getParameter('storage_directory');
            $ruta=$directory . DIRECTORY_SEPARATOR . $entity->getRutaArchivo();
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
