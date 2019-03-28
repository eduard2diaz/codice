<?php

namespace App\DataFixtures;

use App\Entity\Pais;
use App\Entity\Revista;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RevistaFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $revistas = [
            ['nombre' => 'Revista Cubana de Sanidad Animal', 'pais' => 'Cuba', 'impacto' => 1, 'nivel' => 1]
        ];
        foreach ($revistas as $value) {
            $revista = new Revista();
            $pais = $manager->getRepository(Pais::class)->findOneByNombre($value['pais']);

            if (!$pais)
                continue;

            $revista->setNombre($value['nombre']);
            $revista->setPais($pais);
            $revista->setImpacto($value['impacto']);
            $revista->setNivel($value['nivel']);
            $manager->persist($revista);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 6;
    }


}
