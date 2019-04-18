<?php

namespace App\Controller;

use App\Entity\GradoCientifico;
use App\Form\GradoCientificoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/gradocientifico")
 */
class GradoCientificoController extends AbstractController
{
    /**
     * @Route("/", name="grado_cientifico_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $grado_cientificos = $this->getDoctrine()->getRepository(GradoCientifico::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('grado_cientifico/_table.html.twig', [
                'grado_cientificos' => $grado_cientificos,
            ]);

        return $this->render('grado_cientifico/index.html.twig', [
            'grado_cientificos' => $grado_cientificos,
        ]);
    }

    /**
     * @Route("/new", name="grado_cientifico_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $grado_cientifico = new GradoCientifico();
        $form = $this->createForm(GradoCientificoType::class, $grado_cientifico, ['action' => $this->generateUrl('grado_cientifico_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($grado_cientifico);
                $em->flush();
                return $this->json(['mensaje' => 'El grado científico fue registrado satisfactoriamente',
                    'nombre' => $grado_cientifico->getNombre(),
                    'id' => $grado_cientifico->getId(),
                ]);
            } else {
                $page = $this->renderView('grado_cientifico/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('grado_cientifico/_new.html.twig', [
            'grado_cientifico' => $grado_cientifico,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="grado_cientifico_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, GradoCientifico $grado_cientifico): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(GradoCientificoType::class, $grado_cientifico, ['action' => $this->generateUrl('grado_cientifico_edit',['id' => $grado_cientifico->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($grado_cientifico);
                $em->flush();
                return $this->json(['mensaje' => 'El grado científico fue actualizado satisfactoriamente',
                    'nombre' => $grado_cientifico->getNombre(),
                ]);
            } else {
                $page = $this->renderView('grado_cientifico/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'grado_cientifico_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('grado_cientifico/_new.html.twig', [
            'grado_cientifico' => $grado_cientifico,
            'title' => 'Editar grado científico',
            'action' => 'Actualizar',
            'form_id' => 'grado_cientifico_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="grado_cientifico_delete",options={"expose"=true})
     */
    public function delete(Request $request, GradoCientifico $grado_cientifico): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($grado_cientifico);
        $em->flush();
        return $this->json(['mensaje' => 'El grado científico fue eliminado satisfactoriamente']);
    }
}
