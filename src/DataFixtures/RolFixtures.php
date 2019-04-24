<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Rol;

class RolFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $roles=['ROLE_ADMIN','ROLE_DIRECTIVO','ROLE_USER','ROLE_GESTORBALANCE'];
        foreach ($roles as $nombre){
            $rol=new Rol();
            $rol->setNombre($nombre);
            $manager->persist($rol);
        }

        $manager->flush();
    }

    public function getOrder()
    {
     return 1;
    }

}
