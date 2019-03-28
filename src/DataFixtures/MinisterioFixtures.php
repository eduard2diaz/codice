<?php

namespace App\DataFixtures;

use App\Entity\Ministerio;
use App\Entity\Pais;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MinisterioFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $ministerios=[
            ['pais'=>'Cuba','ministerios'=>['Ministerio de Educación Superior', 'Ministerio de la Azúcar']]
        ];
        foreach ($ministerios as $value){
            $pais=$manager->getRepository(Pais::class)->findOneByNombre($value['pais']);
            if(!$pais)
                continue;
            foreach ($value['ministerios'] as $obj){
                $ministerio=new Ministerio();
                $ministerio->setNombre($obj);
                $ministerio->setPais($pais);
                $manager->persist($ministerio);
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
        return 3;
    }
}
