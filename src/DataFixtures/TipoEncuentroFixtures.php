<?php

namespace App\DataFixtures;

use App\Entity\TipoEncuentro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TipoEncuentroFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tipoencuentros=['Superación y formación','Debate'];
        foreach ($tipoencuentros as $value){
            $tipoencuentro=new TipoEncuentro();
            $tipoencuentro->setNombre($value);
            $manager->persist($tipoencuentro);
        }

        $manager->flush();
    }
}
