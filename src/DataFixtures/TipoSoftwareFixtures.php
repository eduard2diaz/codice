<?php

namespace App\DataFixtures;

use App\Entity\TipoSoftware;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TipoSoftwareFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clasificaciones = [
            ['nombre' => 'Software de sistema', 'tiposoftware' => [
                'Cargador de programas', 'Sistemas operativos', 'Controlador de dispositivos',
                'Herramientas de programación', 'Programas utilitarios', 'Entornos de escritorio',
                'BIOS o sistema básico de entrada y salida', 'Hipervisores o máquinas virtuales', 'Gestores de arranque',
            ]],
            ['nombre' => 'Software de programación', 'tiposoftware' => [
                'Compiladores', 'Editores de texto', 'Enlazadores de código',
                'Depuradores', 'Entornos de desarrollo integrado'
            ]],
            ['nombre' => 'Software de aplicación', 'tiposoftware' => [
                'Paquetería o aplicaciones de ofimática', 'Bases de datos','Videojuegos','Software empresarial',
                'Programas o software educativo','Software de gestión o cálculo numérico'
            ]],
        ];
        foreach ($clasificaciones as $clasificacion) {
            $object = $manager->getRepository(\App\Entity\ClasificacionTipoSoftware::class)->findOneByNombre($clasificacion['nombre']);
            if (!$object)
                continue;

            foreach ($clasificacion['tiposoftware'] as $nombre) {
                $tiposoftware = new TipoSoftware();
                $tiposoftware->setNombre($nombre);
                $tiposoftware->setClasificacion($object);
                $manager->persist($tiposoftware);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }


}
