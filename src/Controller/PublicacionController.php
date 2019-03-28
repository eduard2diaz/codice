<?php

namespace App\Controller;

use App\Entity\Publicacion;
use App\Form\PublicacionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/publicacion")
 */
class PublicacionController extends AbstractController
{
    /**
     * @Route("/", name="publicacion_index", methods={"GET"})
     */
    public function index(): Response
    {
        $publicacions = $this->getDoctrine()
            ->getRepository(Publicacion::class)
            ->findAll();

        return $this->render('publicacion/index.html.twig', [
            'publicacions' => $publicacions,
        ]);
    }

    /**
     * @Route("/{id}/show", name="publicacion_show", methods={"GET"})
     */
    public function show(Publicacion $publicacion): Response
    {
        return $this->render('publicacion/show.html.twig', [
            'publicacion' => $publicacion,
        ]);
    }

    /**
     * @Route("/{id}/descargar", name="publicacion_descargar", methods={"GET"})
     */
    public function descargar(Publicacion $publicacion): Response
    {
        $ruta = $this->getParameter('storage_directory') . DIRECTORY_SEPARATOR . $publicacion->getRutaArchivo();

        if (!file_exists($ruta))
            throw $this->createNotFoundException();

        $archivo = file_get_contents($ruta);
        return new Response($archivo, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Transfer-Encoding' => 'binary',
            'Content-length' => strlen($archivo),
            'Pragma' => 'no-cache',
            'Expires' => '0'));
    }

}
