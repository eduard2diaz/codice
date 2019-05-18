<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClasificacionTipoSoftware;

class ClasificacionTipoSoftwareFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clasificaciones=['Software de sistema','Software de programación',
                            'Software de aplicación',
            ];
        foreach ($clasificaciones as $nombre){
            $clasificacion=new ClasificacionTipoSoftware();
            $clasificacion->setNombre($nombre);
            $manager->persist($clasificacion);
        }


        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }


}
