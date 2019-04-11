<?php

namespace App\Controller;

use App\Entity\TipoPremio;
use App\Form\TipoPremioType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tipopremio")
 */
class TipoPremioController extends AbstractController
{
    /**
     * @Route("/", name="tipo_premio_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_premios = $this->getDoctrine()->getRepository(TipoPremio::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_premio/_table.html.twig', [
                'tipo_premios' => $tipo_premios,
            ]);

        return $this->render('tipo_premio/index.html.twig', [
            'tipo_premios' => $tipo_premios,
        ]);
    }

    /**
     * @Route("/new", name="tipo_premio_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $tipo_premio = new TipoPremio();
        $form = $this->createForm(TipoPremioType::class, $tipo_premio, ['action' => $this->generateUrl('tipo_premio_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_premio);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de premio fue registrado satisfactoriamente',
                    'nombre' => $tipo_premio->getNombre(),
                    'id' => $tipo_premio->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_premio/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_premio/_new.html.twig', [
            'tipo_premio' => $tipo_premio,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_premio_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, TipoPremio $tipo_premio): Response
    {
        $form = $this->createForm(TipoPremioType::class, $tipo_premio, ['action' => $this->generateUrl('tipo_premio_edit',['id' => $tipo_premio->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_premio);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de premio fue actualizado satisfactoriamente',
                    'nombre' => $tipo_premio->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_premio/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_premio_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_premio/_new.html.twig', [
            'tipo_premio' => $tipo_premio,
            'title' => 'Editar tipo de premio',
            'action' => 'Actualizar',
            'form_id' => 'tipo_premio_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_premio_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoPremio $tipo_premio): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_premio);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de premio fue eliminado satisfactoriamente']);
    }
}
