<?php

namespace App\DataFixtures;

use App\Entity\TipoEvento;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TipoEncuentroFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tipoencuentros=['Charla','Disertación','Curso','Cursillo','Taller','Seminario',
'Coloquio','Mesa redonda','Panel','Foro','Simposio','Ciclo','Jornada','Exposición','Feria',
'Convención','Asamblea general','Comité','Comisión','Sesión de trabajo','Visita guiada'];
        foreach ($tipoencuentros as $value){
            $tipoencuentro=new TipoEvento();
            $tipoencuentro->setNombre($value);
            $manager->persist($tipoencuentro);
        }

        $manager->flush();
    }
}
