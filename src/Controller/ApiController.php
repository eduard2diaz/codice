<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\Autor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/requesttoken", name="api_requesttoken")
     * Este servicio se puede consumir utilizando curl u otro cliente url como Guzzle, por ejemplo
     * curl -X POST --data "username=untoria&password=untoria" http://localhost/codice/public/index.php/api/requesttoken
     */
    public function requestToken(Request $request, UserPasswordEncoderInterface $encoder)
    {
        if ($request->request->has('username') && $password = $request->request->has('password')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            echo $username;
            $em = $this->getDoctrine()->getManager();
            $autor = $em->getRepository(Autor::class)->findOneByUsuario($username);
            if (!$autor)
                return $this->json(['message' => 'Invalid credentials'], 401);

            $password = $encoder->encodePassword($autor, $password);
            if ($encoder->isPasswordValid($autor, $password))
                return $this->json(['message' => 'Invalid credentials'], 401);

            $token = $em->getRepository(ApiToken::class)->findOneByAutor($autor);
            if (!$token) {
                $token = new ApiToken($autor);
            } else {
                if ($token->isExpired())
                    $token->renewToken();
                $token->renewExpiresAt();
            }

            $em->persist($token);
            $em->flush();
            return $this->json([
                'token' => $token->getToken(),
                'expiresAt' => $token->getExpiresAt()->format('d-m-Y H:i'),
            ], 200);
        }
        return $this->json(['message' => 'Authentication Required'], 401);
    }

    /**
     * @Route("/premio/index", name="api_premio_index")
     * Estos servicios se pueden consumir pasandole el token generado por el metodo anterior y la url a consumir
     * curl -H "X-AUTH-TOKEN: 2411b06c62eae35822e06d0de36af40c354a64083c09c3e0f103a7912a638f4d9325531640bbc973ee3920125606cd321c057838cc3cc893a25f8381"
     * http://localhost/codice/public/index.php/api/premio/index
     */
    public function premioIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Premio l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $premios = $consulta->getResult();
        return $this->json(['premios' => $premios], 200);
    }

    /**
     * @Route("/encuentro/index", name="api_encuentro_index")
     */
    public function encuentroIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Encuentro l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $encuentros = $consulta->getResult();
        return $this->json(['encuentros' => $encuentros], 200);
    }

    /**
     * @Route("/tesis/index", name="api_tesis_index")
     */
    public function tesisIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Tesis l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $tesis = $consulta->getResult();
        return $this->json(['tesis' => $tesis], 200);
    }

    /**
     * @Route("/software/index", name="api_software_index")
     */
    public function softwareIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Software l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $softwares = $consulta->getResult();
        return $this->json(['softwares' => $softwares], 200);
    }

    /**
     * @Route("/patente/index", name="api_patente_index")
     */
    public function patenteIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Patente l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $patentes = $consulta->getResult();
        return $this->json(['patentes' => $patentes], 200);
    }

    /**
     * @Route("/norma/index", name="api_norma_index")
     */
    public function normaIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Norma l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $normas = $consulta->getResult();
        return $this->json(['normas' => $normas], 200);
    }

    /**
     * @Route("/monografia/index", name="api_monografia_index")
     */
    public function monografiaIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Monografia l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $monografias = $consulta->getResult();
        return $this->json(['monografias' => $monografias], 200);
    }

    /**
     * @Route("/libro/index", name="api_libro_index")
     */
    public function libroIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Libro l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $libros = $consulta->getResult();
        return $this->json(['libros' => $libros], 200);
    }

    /**
     * @Route("/articulo/index", name="api_articulo_index")
     */
    public function articuloIndex()
    {
        $autor = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Libro l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $articulos = $consulta->getResult();
        return $this->json(['articulos' => $articulos], 200);
    }

    /**
     * @Route("/publicacion/{id}/show", name="api_publicacion_show")
     */
    public function publicacionShow($id)
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT p.titulo, p.resumen, p.keywords FROM App:Publicacion p WHERE p.id= :id');
        $consulta->setParameter('id', $id);
        $consulta->setMaxResults(1);
        $publicacion = $consulta->getResult();
        return $this->json(['publicacion' => $publicacion], 200);
    }
}
