<?php

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\Institucion;
use App\Entity\Ministerio;
use App\Entity\Pais;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AreaFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $areas = [
            ['padre' => null, 'nombre' => 'Decanato', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Técnicas', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Veterinaria', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Agronomía', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Sociales y Humanísticas', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Cultural Física', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad Pedagógica', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
            ['padre' => 'Decanato', 'nombre' => 'Facultad de Ciencias Económias y Empresariales', 'pais' => 'Cuba', 'ministerio' => 'Ministerio de Educación Superior', 'institucion' => 'Universidad Agraria de La Habana'],
        ];
        foreach ($areas as $value) {
            $area = new Area();
            $institucion = $manager->getRepository(Institucion::class)->findOneByNombre($value['institucion']);
            $ministerio = $manager->getRepository(Ministerio::class)->findOneByNombre($value['ministerio']);
            $pais = $manager->getRepository(Pais::class)->findOneByNombre($value['pais']);
            $area->setInstitucion($institucion);
            $area->setMinisterio($ministerio);
            $area->setPais($pais);
            if (null != $value['padre']) {
                $padre = $manager->getRepository(Area::class)->findOneByNombre($value['padre']);
                $area->setPadre($padre);
            }
            $area->setNombre($value['nombre']);
            $manager->persist($area);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 5;
    }


}
