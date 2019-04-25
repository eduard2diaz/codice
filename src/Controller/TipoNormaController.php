<?php

namespace App\Controller;

use App\Entity\TipoNorma;
use App\Form\TipoNormaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tiponorma")
 */
class TipoNormaController extends AbstractController
{
    /**
     * @Route("/", name="tipo_norma_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_normas = $this->getDoctrine()->getRepository(TipoNorma::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_norma/_table.html.twig', [
                'tipo_normas' => $tipo_normas,
            ]);

        return $this->render('tipo_norma/index.html.twig', [
            'tipo_normas' => $tipo_normas,
        ]);
    }

    /**
     * @Route("/new", name="tipo_norma_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $tipo_norma = new TipoNorma();
        $form = $this->createForm(TipoNormaType::class, $tipo_norma, ['action' => $this->generateUrl('tipo_norma_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_norma);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de norma fue registrado satisfactoriamente',
                    'nombre' => $tipo_norma->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$tipo_norma->getId())->getValue(),
                    'id' => $tipo_norma->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_norma/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_norma/_new.html.twig', [
            'tipo_norma' => $tipo_norma,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_norma_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, TipoNorma $tipo_norma): Response
    {
        $form = $this->createForm(TipoNormaType::class, $tipo_norma, ['action' => $this->generateUrl('tipo_norma_edit',['id' => $tipo_norma->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_norma);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de norma fue actualizado satisfactoriamente',
                    'nombre' => $tipo_norma->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_norma/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_norma_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_norma/_new.html.twig', [
            'tipo_norma' => $tipo_norma,
            'title' => 'Editar tipo de norma',
            'action' => 'Actualizar',
            'form_id' => 'tipo_norma_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_norma_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoNorma $tipo_norma): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$tipo_norma->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_norma);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de norma fue eliminado satisfactoriamente']);
    }
}
