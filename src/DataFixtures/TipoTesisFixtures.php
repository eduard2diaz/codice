<?php

namespace App\DataFixtures;

use App\Entity\TipoTesis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClasificacionTipoTesis;

class TipoTesisFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clasificaciones = [
            ['nombre' => 'Por su nivel de estudios', 'tipotesis' => [
                'Tesis doctoral', 'Tesis de maestría', 'Tesis de licenciatura'
            ]],
            ['nombre' => 'Por el tratamiento de su tema', 'tipotesis' => [
                'Tesis sobre temas teóricos', 'Tesis sobre temas prácticos', 'Tesis de laboratorio',
                'Tesis derivadas de observaciones', 'Tesis con temas teórico-prácticos', 'Tesis con temas intuitivos',
                'Tesis sobre aspectos filosóficos', 'Tesis de áreas específicas', 'Tesis de temas concretos',
                'Tesis multidisciplinarias',
            ]],
            ['nombre' => 'Por el método de investigación', 'tipotesis' => [
                'Tesis de investigación documental Teórica', 'Tesis de investigación de campo Práctica',
                'Tesis combinada de investigación documental y de campo'
            ]],
            ['nombre' => 'Por el manejo de la información', 'tipotesis' => [
                'Tesis transcriptivas', 'Tesis narrativas', 'Tesis expositivas', 'Tesis de punto final', 'Tesis catálogo',
                'Tesis históricas', 'Tesis utópicas', 'Tesis audaces', 'Tesis mosaico', 'Tesis de técnicas mixtas'
            ]],
        ];
        foreach ($clasificaciones as $clasificacion) {
            $object = $manager->getRepository(ClasificacionTipoTesis::class)->findOneByNombre($clasificacion['nombre']);
            if (!$object)
                continue;

            foreach ($clasificacion['tipotesis'] as $nombre) {
                $tipotesis = new TipoTesis();
                $tipotesis->setNombre($nombre);
                $tipotesis->setClasificacion($object);
                $manager->persist($tipotesis);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }


}
