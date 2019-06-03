<?php

namespace App\DataFixtures;

use App\Entity\Idioma;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class IdiomaFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $idiomas=['Español','Inglés','Francés','Italiano','Portugués'];
        foreach ($idiomas as $value){
            $idioma=new Idioma();
            $idioma->setNombre($value);
            $manager->persist($idioma);
        }

        $manager->flush();
    }
}
