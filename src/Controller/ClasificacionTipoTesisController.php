<?php

namespace App\Controller;

use App\Entity\ClasificacionTipoTesis;
use App\Form\ClasificacionTipoTesisType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/clasificaciontipotesis")
 */
class ClasificacionTipoTesisController extends AbstractController
{
    /**
     * @Route("/", name="clasificacion_tipotesis_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $clasificacion_tipotesis = $this->getDoctrine()->getRepository(ClasificacionTipoTesis::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('clasificacion_tipotesis/_table.html.twig', [
                'clasificacion_tipotesiss' => $clasificacion_tipotesis,
            ]);

        return $this->render('clasificacion_tipotesis/index.html.twig', [
            'clasificacion_tipotesiss' => $clasificacion_tipotesis,
        ]);
    }

    /**
     * @Route("/new", name="clasificacion_tipotesis_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $clasificacion_tipotesis = new ClasificacionTipoTesis();
        $form = $this->createForm(ClasificacionTipoTesisType::class, $clasificacion_tipotesis, ['action' => $this->generateUrl('clasificacion_tipotesis_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($clasificacion_tipotesis);
                $em->flush();
                return $this->json(['mensaje' => 'La clasificación fue registrada satisfactoriamente',
                    'nombre' => $clasificacion_tipotesis->getNombre(),
                    'id' => $clasificacion_tipotesis->getId(),
                ]);
            } else {
                $page = $this->renderView('clasificacion_tipotesis/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('clasificacion_tipotesis/_new.html.twig', [
            'clasificacion_tipotesis' => $clasificacion_tipotesis,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="clasificacion_tipotesis_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, ClasificacionTipoTesis $clasificacion_tipotesis): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(ClasificacionTipoTesisType::class, $clasificacion_tipotesis, ['action' => $this->generateUrl('clasificacion_tipotesis_edit',['id' => $clasificacion_tipotesis->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($clasificacion_tipotesis);
                $em->flush();
                return $this->json(['mensaje' => 'La clasificación fue actualizada satisfactoriamente',
                    'nombre' => $clasificacion_tipotesis->getNombre(),
                ]);
            } else {
                $page = $this->renderView('clasificacion_tipotesis/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'clasificacion_tipotesis_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('clasificacion_tipotesis/_new.html.twig', [
            'clasificacion_tipotesis' => $clasificacion_tipotesis,
            'title' => 'Editar clasificación',
            'action' => 'Actualizar',
            'form_id' => 'clasificacion_tipotesis_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="clasificacion_tipotesis_delete",options={"expose"=true})
     */
    public function delete(Request $request, ClasificacionTipoTesis $clasificacion_tipotesis): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($clasificacion_tipotesis);
        $em->flush();
        return $this->json(['mensaje' => 'La clasificación fue eliminada satisfactoriamente']);
    }
}
