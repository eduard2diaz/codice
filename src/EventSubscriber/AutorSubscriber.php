<?php

namespace App\EventSubscriber;

use App\Entity\Autor;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;


class AutorSubscriber implements EventSubscriber
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
        if ($entity instanceof Autor){
            $entity->setPassword($this->getServiceContainer()->get('security.password_encoder')->encodePassword($entity,$entity->getPassword()));
            $entity->Upload($this->getServiceContainer()->getParameter('storage_directory'));
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }
}
