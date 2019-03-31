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
        $rol=$manager->getRepository(Rol::class)->findOneByNombre('ROLE_ADMIN');
        $area=$manager->getRepository(Area::class)->findOneByNombre('Decanato');
        $grado=$manager->getRepository(GradoCientifico::class)->findOneByNombre('Doctor en Ciencias');
        $institucion=$manager->getRepository(Institucion::class)->findOneByNombre('Universidad Agraria de La Habana');
        $autor->setPais($institucion->getMinisterio()->getPais());
        $autor->setMinisterio($institucion->getMinisterio());
        $autor->setUsuario('administrador');
        $autor->setInstitucion($institucion);
        $autor->setNombre('administrador');
        $autor->setArea($area);
        $autor->setPassword('administrador');
        $autor->setGradoCientifico($grado);
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
