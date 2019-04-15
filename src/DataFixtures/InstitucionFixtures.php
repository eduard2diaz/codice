<?php

namespace App\DataFixtures;

use App\Entity\Institucion;
use App\Entity\Ministerio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class InstitucionFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $instituciones=[
            ['ministerio'=>'Ministerio de Educación Superior','instituciones'=>['Universidad Agraria de La Habana', 'Instituto de Ciencia Animal','Centro Nacional de Sanidad Agropecuaria',]]
        ];
        foreach ($instituciones as $value){
            $ministerio=$manager->getRepository(Ministerio::class)->findOneByNombre($value['ministerio']);
            if(!$ministerio)
                continue;

            foreach ($value['instituciones'] as $obj){
                $institucion=new Institucion();
                $institucion->setNombre($obj);
                $institucion->setMinisterio($ministerio);
                $institucion->setPais($ministerio->getPais());
                $manager->persist($institucion);
            }
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
        return 4;
    }
}
