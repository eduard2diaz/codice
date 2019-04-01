<?php

namespace App\Controller;

use App\Entity\Area;
use App\Form\AreaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/area")
 */
class AreaController extends AbstractController
{
    /**
     * @Route("/", name="area_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $areas = $this->getDoctrine()->getRepository(Area::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('area/_table.html.twig', [
                'areas' => $areas,
            ]);

        return $this->render('area/index.html.twig', [
            'areas' => $areas,
        ]);
    }

    /**
     * @Route("/new", name="area_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $area = new Area();
        $form = $this->createForm(AreaType::class, $area, array('action' => $this->generateUrl('area_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($area);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El área fue registrada satisfactoriamente',
                    'nombre' => $area->getNombre(),
                    'padre' => $area->getPadre()!=null ? $area->getPadre()->getNombre() : '',
                    'id' => $area->getId(),
                ));
            } else {
                $page = $this->renderView('area/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="area_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Area $area): Response
    {
        $form = $this->createForm(AreaType::class, $area, array('action' => $this->generateUrl('area_edit',array('id' => $area->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($area);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El área fue actualizada satisfactoriamente',
                    'nombre' => $area->getNombre(),
                    'padre' => $area->getPadre()!=null ? $area->getPadre()->getNombre() : ''
                ));
            } else {
                $page = $this->renderView('area/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'area_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'title' => 'Editar área',
            'action' => 'Actualizar',
            'form_id' => 'area_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="area_delete",options={"expose"=true})
     */
    public function delete(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($area);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El área fue eliminada satisfactoriamente'));
    }
}
