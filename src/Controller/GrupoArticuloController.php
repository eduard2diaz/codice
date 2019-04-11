<?php

namespace App\Controller;

use App\Entity\GrupoArticulo;
use App\Form\GrupoArticuloType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/grupoarticulo")
 */
class GrupoArticuloController extends AbstractController
{
    /**
     * @Route("/", name="grupo_articulo_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $grupo_articulos = $this->getDoctrine()->getRepository(GrupoArticulo::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('grupo_articulo/_table.html.twig', [
                'grupo_articulos' => $grupo_articulos,
            ]);

        return $this->render('grupo_articulo/index.html.twig', [
            'grupo_articulos' => $grupo_articulos,
        ]);
    }

    /**
     * @Route("/new", name="grupo_articulo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $grupo_articulo = new GrupoArticulo();
        $form = $this->createForm(GrupoArticuloType::class, $grupo_articulo, ['action' => $this->generateUrl('grupo_articulo_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($grupo_articulo);
                $em->flush();
                return $this->json(['mensaje' => 'El grupo del artículo fue registrado satisfactoriamente',
                    'nombre' => $grupo_articulo->getNombre(),
                    'id' => $grupo_articulo->getId(),
                ]);
            } else {
                $page = $this->renderView('grupo_articulo/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('grupo_articulo/_new.html.twig', [
            'grupo_articulo' => $grupo_articulo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="grupo_articulo_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, GrupoArticulo $grupo_articulo): Response
    {
        $form = $this->createForm(GrupoArticuloType::class, $grupo_articulo, ['action' => $this->generateUrl('grupo_articulo_edit',['id' => $grupo_articulo->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($grupo_articulo);
                $em->flush();
                return $this->json(['mensaje' => 'El grupo del artículo fue actualizado satisfactoriamente',
                    'nombre' => $grupo_articulo->getNombre(),
                ]);
            } else {
                $page = $this->renderView('grupo_articulo/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'grupo_articulo_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('grupo_articulo/_new.html.twig', [
            'grupo_articulo' => $grupo_articulo,
            'title' => 'Editar grupo del artículo',
            'action' => 'Actualizar',
            'form_id' => 'grupo_articulo_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="grupo_articulo_delete",options={"expose"=true})
     */
    public function delete(Request $request, GrupoArticulo $grupo_articulo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($grupo_articulo);
        $em->flush();
        return $this->json(['mensaje' => 'El grupo del artículo fue eliminado satisfactoriamente']);
    }
}
