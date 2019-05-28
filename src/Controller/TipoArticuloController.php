<?php

namespace App\Controller;

use App\Entity\TipoArticulo;
use App\Form\TipoArticuloType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tipoarticulo")
 */
class TipoArticuloController extends AbstractController
{
    /**
     * @Route("/", name="tipo_articulo_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_articulos = $this->getDoctrine()->getRepository(TipoArticulo::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_articulo/_table.html.twig', [
                'tipo_articulos' => $tipo_articulos,
            ]);

        return $this->render('tipo_articulo/index.html.twig', [
            'tipo_articulos' => $tipo_articulos,
        ]);
    }

    /**
     * @Route("/new", name="tipo_articulo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $tipo_articulo = new TipoArticulo();
        $form = $this->createForm(TipoArticuloType::class, $tipo_articulo, ['action' => $this->generateUrl('tipo_articulo_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_articulo);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de artículo fue registrado satisfactoriamente',
                    'nombre' => $tipo_articulo->getNombre(),
                    'grupo' => $tipo_articulo->getGrupo()->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$tipo_articulo->getId())->getValue(),
                    'id' => $tipo_articulo->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_articulo/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_articulo/_new.html.twig', [
            'tipo_articulo' => $tipo_articulo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_articulo_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, TipoArticulo $tipo_articulo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(TipoArticuloType::class, $tipo_articulo, ['action' => $this->generateUrl('tipo_articulo_edit',['id' => $tipo_articulo->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_articulo);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de artículo fue actualizado satisfactoriamente',
                    'nombre' => $tipo_articulo->getNombre(),
                    'grupo' => $tipo_articulo->getGrupo()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_articulo/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_articulo_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_articulo/_new.html.twig', [
            'tipo_articulo' => $tipo_articulo,
            'title' => 'Editar tipo de artículo',
            'action' => 'Actualizar',
            'form_id' => 'tipo_articulo_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_articulo_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoArticulo $tipo_articulo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$tipo_articulo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_articulo);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de artículo fue eliminado satisfactoriamente']);
    }
}
