<?php

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\Autor;
use App\Entity\GradoCientifico;
use App\Entity\Institucion;
use App\Entity\Rol;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AutorFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $autor=new Autor();
        $rol=$manager->getRepository(Rol::class)->findOneByNombre('ROLE_SUPERADMIN');
        /*$area=$manager->getRepository(Area::class)->findOneByNombre('Decanato');
        $grado=$manager->getRepository(GradoCientifico::class)->findOneByNombre('Doctor en Ciencias');
        $institucion=$manager->getRepository(Institucion::class)->findOneByNombre('Universidad Agraria de La Habana');
        $autor->setPais($institucion->getMinisterio()->getPais());
        $autor->setMinisterio($institucion->getMinisterio());
        $autor->setInstitucion($institucion);
        $autor->setArea($area);
        $autor->setGradoCientifico($grado);
        */
        $autor->setUsuario('administrador');
        $autor->setNombre('administrador');
        $autor->setPassword('administrador');
        $autor->setEmail('administrador@unah.edu.cu');
        $autor->addIdrol($rol);
        $manager->persist($autor);
        $manager->flush();
    }

    public function getOrder()
    {
     return 6;
    }


}
