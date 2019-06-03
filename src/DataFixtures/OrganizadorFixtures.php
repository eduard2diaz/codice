<?php

namespace App\DataFixtures;

use App\Entity\Organizador;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class OrganizadorFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $organizadores=['CITMA','Unión de Informáticos de Cuba'];
        foreach ($organizadores as $value){
            $organizador=new Organizador();
            $organizador->setNombre($value);
            $manager->persist($organizador);
        }
        $manager->flush();
    }
}
