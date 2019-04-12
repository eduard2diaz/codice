<?php

namespace App\DataFixtures;

use App\Entity\GrupoArticulo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class GrupoArticuloFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $grupos = ['Científico'];
        foreach ($grupos as $value) {
            $grupo = new GrupoArticulo();
            $grupo->setNombre($value);
            $manager->persist($grupo);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
