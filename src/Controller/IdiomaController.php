<?php

namespace App\Controller;

use App\Entity\Idioma;
use App\Form\IdiomaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/idioma")
 */
class IdiomaController extends AbstractController
{
    /**
     * @Route("/", name="idioma_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $idiomas = $this->getDoctrine()->getRepository(Idioma::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('idioma/_table.html.twig', [
                'idiomas' => $idiomas,
            ]);

        return $this->render('idioma/index.html.twig', [
            'idiomas' => $idiomas,
        ]);
    }

    /**
     * @Route("/new", name="idioma_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $idioma = new Idioma();
        $form = $this->createForm(IdiomaType::class, $idioma, ['action' => $this->generateUrl('idioma_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($idioma);
                $em->flush();
                return $this->json(['mensaje' => 'El idioma fue registrado satisfactoriamente',
                    'nombre' => $idioma->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$idioma->getId())->getValue(),
                    'id' => $idioma->getId(),
                ]);
            } else {
                $page = $this->renderView('idioma/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('idioma/_new.html.twig', [
            'idioma' => $idioma,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="idioma_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Idioma $idioma): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(IdiomaType::class, $idioma, ['action' => $this->generateUrl('idioma_edit',['id' => $idioma->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($idioma);
                $em->flush();
                return $this->json(['mensaje' => 'El idioma fue actualizado satisfactoriamente',
                    'nombre' => $idioma->getNombre(),
                ]);
            } else {
                $page = $this->renderView('idioma/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'idioma_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('idioma/_new.html.twig', [
            'idioma' => $idioma,
            'title' => 'Editar idioma',
            'action' => 'Actualizar',
            'form_id' => 'idioma_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="idioma_delete",options={"expose"=true})
     */
    public function delete(Request $request, Idioma $idioma): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$idioma->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($idioma);
        $em->flush();
        return $this->json(['mensaje' => 'El idioma fue eliminado satisfactoriamente']);
    }
}
