<?php

namespace App\DataFixtures;

use App\Entity\TipoNorma;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TipoNormaFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tiponormas=['Internacional','Empresarial','Sectorial'];
        foreach ($tiponormas as $value){
            $tiponorma=new TipoNorma();
            $tiponorma->setNombre($value);
            $manager->persist($tiponorma);
        }
        $manager->flush();
    }
}
