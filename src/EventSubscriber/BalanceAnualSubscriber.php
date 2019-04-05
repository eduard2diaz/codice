<?php

namespace App\EventSubscriber;

use App\Entity\BalanceAnual;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;

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
        if ($entity instanceof BalanceAnual){
            $entity->Upload($this->getServiceContainer()->getParameter('storage_directory'));
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof BalanceAnual) {
            $fs = new Filesystem();
            $directory = $this->getServiceContainer()->getParameter('storage_directory');
            $fs->remove($directory . DIRECTORY_SEPARATOR . $entity->getRutaArchivo());
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
