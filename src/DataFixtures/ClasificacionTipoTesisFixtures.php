<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClasificacionTipoTesis;

class ClasificacionTipoTesisFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clasificaciones=['Por su nivel de estudios','Por el tratamiento de su tema',
                            'Por el método de investigación','Por el manejo de la información',
            ];
        foreach ($clasificaciones as $nombre){
            $clasificacion=new ClasificacionTipoTesis();
            $clasificacion->setNombre($nombre);
            $manager->persist($clasificacion);
        }


        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }


}
