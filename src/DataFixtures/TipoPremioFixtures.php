<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TipoPremio;

class TipoPremioFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $roles=['Premios de Arte','Premios de automóviles‎','Concursos de belleza‎','Premios de ciencia e ingeniería‎',
            'Premios de ciencias sociales‎','Premios de comunicación‎','Premios y trofeos deportivos‎','Premios de derecho‎',
            'Premios de diseño‎','Premios de economía‎','Premios al mérito académico‎','Premios educativos‎','Premios empresariales‎',
            'Premios de humanidades‎','Premios al mérito humanitario y de servicio‎','Premios irónicos y humorísticos‎',
            'Premios de igualdad‎','Premios de juegos‎','Premios de medio ambiente‎','Premios de medios audiovisuales‎',
            'Premios ambientales‎','Premios por país y tipo‎','Premios Scout‎','Premios de temática religiosa‎',
            'Premios LGBT'
            ];
        foreach ($roles as $nombre){
            $rol=new TipoPremio();
            $rol->setNombre($nombre);
            $manager->persist($rol);
        }

        $manager->flush();
    }

    public function getOrder()
    {
     return 7;
    }

}
