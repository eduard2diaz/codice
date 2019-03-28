<?php

namespace App\Controller;

use App\Entity\Pais;
use App\Form\PaisType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/pais")
 */
class PaisController extends AbstractController
{
    /**
     * @Route("/", name="pais_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $paiss = $this->getDoctrine()->getRepository(Pais::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('pais/_table.html.twig', [
                'paiss' => $paiss,
            ]);

        return $this->render('pais/index.html.twig', [
            'paiss' => $paiss,
        ]);
    }

    /**
     * @Route("/new", name="pais_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pais = new Pais();
        $form = $this->createForm(PaisType::class, $pais, array('action' => $this->generateUrl('pais_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($pais);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El país fue registrado satisfactoriamente',
                    'nombre' => $pais->getNombre(),
                    'capital' => $pais->getCapital(),
                    'codigo' => $pais->getCodigo(),
                    'id' => $pais->getId(),
                ));
            } else {
                $page = $this->renderView('pais/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('pais/_new.html.twig', [
            'pais' => $pais,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pais_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Pais $pais): Response
    {
        $form = $this->createForm(PaisType::class, $pais, array('action' => $this->generateUrl('pais_edit',array('id' => $pais->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($pais);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El país fue actualizado satisfactoriamente',
                    'nombre' => $pais->getNombre(),
                    'capital' => $pais->getCapital(),
                    'codigo' => $pais->getCodigo(),
                ));
            } else {
                $page = $this->renderView('pais/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'pais_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('pais/_new.html.twig', [
            'pais' => $pais,
            'title' => 'Editar país',
            'action' => 'Actualizar',
            'form_id' => 'pais_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="pais_delete",options={"expose"=true})
     */
    public function delete(Request $request, Pais $pais): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($pais);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El país fue eliminado satisfactoriamente'));
    }
}
