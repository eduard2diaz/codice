<?php

namespace App\DataFixtures;

use App\Entity\GradoCientifico;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GradoCientificoFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $gradocientificos=['Master en Ciencias','Doctor en Ciencias'];
        foreach ($gradocientificos as $value){
            $grado=new GradoCientifico();
            $grado->setNombre($value);
            $manager->persist($grado);
        }
        $manager->flush();
    }
}
