<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UsuarioFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $usuario=new Usuario();
        $usuario->setUsuario('administrador');
        $usuario->setNombre('administrador');
        $usuario->setPassword('administrador');
        $usuario->setEmail('administrador@unah.edu.cu');
        $manager->persist($usuario);
        $manager->flush();
    }

}
