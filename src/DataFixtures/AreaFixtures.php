<?php

namespace App\DataFixtures;

use App\Entity\Area;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AreaFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $areas = [
            ['padre'=>null,'nombre' =>'Decanato'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Técnicas'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Veterinaria'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Agronomía'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Sociales y Humanísticas'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Cultural Física'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad Pedagógica'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Económias y Empresariales'],
        ];
        foreach ($areas as $value) {
            $area = new Area();
            if (null!=$value['padre']) {
                $padre = $manager->getRepository(Area::class)->findOneByNombre($value['padre']);
                $area->setPadre($padre);
            }
            $area->setNombre($value['nombre']);
            $manager->persist($area);
            $manager->flush();
        }
    }
}
