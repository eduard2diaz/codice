<?php

namespace App\Controller;

use App\Entity\Institucion;
use App\Entity\Ministerio;
use App\Form\InstitucionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/institucion")
 */
class InstitucionController extends AbstractController
{
    /**
     * @Route("/", name="institucion_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $institucions = $this->getDoctrine()->getRepository(Institucion::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('institucion/_table.html.twig', [
                'institucions' => $institucions,
            ]);

        return $this->render('institucion/index.html.twig', [
            'institucions' => $institucions,
        ]);
    }

    /**
     * @Route("/new", name="institucion_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $institucion = new Institucion();
        $form = $this->createForm(InstitucionType::class, $institucion, array('action' => $this->generateUrl('institucion_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($institucion);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'La institución fue registrada satisfactoriamente',
                    'nombre' => $institucion->getNombre(),
                    'pais' => $institucion->getPais()->getNombre(),
                    'ministerio' => $institucion->getMinisterio()->getNombre(),
                    'id' => $institucion->getId(),
                ));
            } else {
                $page = $this->renderView('institucion/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('institucion/_new.html.twig', [
            'institucion' => $institucion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="institucion_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Institucion $institucion): Response
    {
        $form = $this->createForm(InstitucionType::class, $institucion, array('action' => $this->generateUrl('institucion_edit',array('id' => $institucion->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($institucion);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'La institución fue actualizada satisfactoriamente',
                    'nombre' => $institucion->getNombre(),
                    'pais' => $institucion->getPais()->getNombre(),
                    'ministerio' => $institucion->getMinisterio()->getNombre(),
                ));
            } else {
                $page = $this->renderView('institucion/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'institucion_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('institucion/_new.html.twig', [
            'institucion' => $institucion,
            'title' => 'Editar institución',
            'action' => 'Actualizar',
            'form_id' => 'institucion_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="institucion_delete",options={"expose"=true})
     */
    public function delete(Request $request, Institucion $institucion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($institucion);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'La institución fue eliminada satisfactoriamente'));
    }

    //Funcionalidades ajax
    /**
     * @Route("/{id}/findbyministerio", name="institucion_findbyministerio",options={"expose"=true})
     * Funcioanalidad que retorna el listado de instituciones que pertenecen a un determinado ministerio(
     * SE UTILIZA EN EL GESTIONAR Autor)
     */
    public function findbyministerio(Request $request, Ministerio $ministerio): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $instituciones=$em->getRepository(Institucion::class)->findByMinisterio($ministerio);

        $instituciones_array=[];
        foreach ($instituciones as $institucion)
            $instituciones_array[]=['id'=>$institucion->getId(),'nombre'=>$institucion->getNombre()];

        return new JsonResponse($instituciones_array);
    }
}
