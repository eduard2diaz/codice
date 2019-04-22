<?php

namespace App\Controller;

use App\Entity\Editorial;
use App\Entity\Libro;
use App\Form\EditorialType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $editorial = new Editorial();
        $form = $this->createForm(EditorialType::class, $editorial, ['action' => $this->generateUrl('editorial_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($editorial);
                $em->flush();
                return $this->json(['mensaje' => 'La editorial fue registrada satisfactoriamente',
                    'nombre' => $editorial->getNombre(),
                    'pais' => $editorial->getPais()->getNombre(),
                    'id' => $editorial->getId(),
                ]);
            } else {
                $page = $this->renderView('editorial/_form.html.twig', [
                    'form' => $form->createView(),
                    'editorial' => $editorial,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(EditorialType::class, $editorial, ['action' => $this->generateUrl('editorial_edit',['id' => $editorial->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($editorial);
                $em->flush();
                return $this->json(['mensaje' => 'La editorial fue actualizada satisfactoriamente',
                    'nombre' => $editorial->getNombre(),
                    'pais' => $editorial->getPais()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('editorial/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'editorial_edit',
                    'action' => 'Actualizar',
                    'editorial' => $editorial,
                    'eliminable'=>$this->esEliminable($editorial)
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('editorial/_new.html.twig', [
            'editorial' => $editorial,
            'title' => 'Editar editorial',
            'action' => 'Actualizar',
            'form_id' => 'editorial_edit',
            'form' => $form->createView(),
            'eliminable'=>$this->esEliminable($editorial)
        ]);
    }

    /**
     * @Route("/{id}/delete", name="editorial_delete",options={"expose"=true})
     */
    public function delete(Request $request, Editorial $editorial): Response
    {
        if (!$request->isXmlHttpRequest() || false==$this->esEliminable($editorial) || !$this->isCsrfTokenValid('delete'.$editorial->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($editorial);
        $em->flush();
        return $this->json(['mensaje' => 'La editorial fue eliminada satisfactoriamente']);
    }

    /*
     * Funcion privada que devuelve si una editorial es eliminable
     */
    private function esEliminable(Editorial $editorial){
        return $this->getDoctrine()->getManager()
                ->getRepository(Libro::class)
                ->findOneByEditorial($editorial)==null;
    }
}
