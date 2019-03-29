<?php

namespace App\Controller;

use App\Entity\Editorial;
use App\Form\EditorialType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/editorial")
 */
class EditorialController extends AbstractController
{
    /**
     * @Route("/", name="editorial_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $editorials = $this->getDoctrine()->getRepository(Editorial::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('editorial/_table.html.twig', [
                'editorials' => $editorials,
            ]);

        return $this->render('editorial/index.html.twig', [
            'editorials' => $editorials,
        ]);
    }

    /**
     * @Route("/new", name="editorial_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $editorial = new Editorial();
        $form = $this->createForm(EditorialType::class, $editorial, array('action' => $this->generateUrl('editorial_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($editorial);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'La editorial fue registrada satisfactoriamente',
                    'nombre' => $editorial->getNombre(),
                    'pais' => $editorial->getPais()->getNombre(),
                    'id' => $editorial->getId(),
                ));
            } else {
                $page = $this->renderView('editorial/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('editorial/_new.html.twig', [
            'editorial' => $editorial,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="editorial_show", methods={"GET"},options={"expose"=true})
     */
    public function show(Request $request, Editorial $editorial): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('editorial/_show.html.twig', [
            'editorial' => $editorial,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="editorial_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Editorial $editorial): Response
    {
        $form = $this->createForm(EditorialType::class, $editorial, array('action' => $this->generateUrl('editorial_edit',array('id' => $editorial->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($editorial);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'La editorial fue actualizada satisfactoriamente',
                    'nombre' => $editorial->getNombre(),
                    'pais' => $editorial->getPais()->getNombre(),
                ));
            } else {
                $page = $this->renderView('editorial/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'editorial_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('editorial/_new.html.twig', [
            'editorial' => $editorial,
            'title' => 'Editar editorial',
            'action' => 'Actualizar',
            'form_id' => 'editorial_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="editorial_delete",options={"expose"=true})
     */
    public function delete(Request $request, Editorial $editorial): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($editorial);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'La editorial fue eliminada satisfactoriamente'));
    }
}
