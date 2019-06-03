<?php

namespace App\DataFixtures;

use App\Entity\GrupoArticulo;
use App\Entity\TipoArticulo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TipoArticuloFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $tipos = [
            [
                'grupo' => 'Científico',
                'tipo' => [
                    'Revisión sistemática', 'Estudios clínicos randomizados', 'Estudio de cohortes', 'Estudios de incidencia',
                    'Estudio caso control', 'Estudio de serie de pacientes',
                    'Revisión narrativa']
            ]

        ];

        foreach ($tipos as $value) {
            $grupo = $manager->getRepository(GrupoArticulo::class)->findOneByNombre($value['grupo']);
            foreach ($value['tipo'] as $obj) {
                $tipo = new TipoArticulo();
                $tipo->setNombre($obj);
                $tipo->setGrupo($grupo);
                $manager->persist($tipo);
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
        return 2;
    }
}
