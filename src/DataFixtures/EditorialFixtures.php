<?php

namespace App\DataFixtures;

use App\Entity\Editorial;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Pais;

class EditorialFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $editoriales=[
            ['pais'=>'Cuba','correo'=>'editorial@uci.cu','nombre'=>'Félix Varela','direccion'=>'La Habana']
        ];
        foreach ($editoriales as $value){
            $pais=$manager->getRepository(Pais::class)->findOneByNombre($value['pais']);
            if(!$pais)
                continue;
                $editorial=new Editorial();
                $editorial->setNombre($value['nombre']);
                $editorial->setPais($pais);
                $editorial->setDireccion($value['direccion']);
                $editorial->setCorreo($value['correo']);
                $manager->persist($editorial);
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
       return 6;
    }
}
