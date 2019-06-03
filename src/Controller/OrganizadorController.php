<?php

namespace App\Controller;

use App\Entity\Evento;
use App\Entity\Organizador;
use App\Form\OrganizadorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organizador")
 */
class OrganizadorController extends AbstractController
{
    /**
     * @Route("/", name="organizador_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $organizadors = $this->getDoctrine()->getRepository(Organizador::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('organizador/_table.html.twig', [
                'organizadors' => $organizadors,
            ]);

        return $this->render('organizador/index.html.twig', [
            'organizadors' => $organizadors,
        ]);
    }

    /**
     * @Route("/new", name="organizador_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $organizador = new Organizador();
        $form = $this->createForm(OrganizadorType::class, $organizador, ['action' => $this->generateUrl('organizador_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($organizador);
                $em->flush();
                return $this->json(['mensaje' => 'El organizador fue registrado satisfactoriamente',
                    'nombre' => $organizador->getNombre(),
                    'id' => $organizador->getId(),
                ]);
            } else {
                $page = $this->renderView('organizador/_form.html.twig', [
                    'form' => $form->createView(),
                    'organizador' => $organizador,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('organizador/_new.html.twig', [
            'organizador' => $organizador,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="organizador_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Organizador $organizador): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(OrganizadorType::class, $organizador, ['action' => $this->generateUrl('organizador_edit',['id' => $organizador->getId()])]);
        $form->handleRequest($request);
        $eliminable=$this->esEliminable($organizador);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organizador);
                $em->flush();
                return $this->json(['mensaje' => 'El organizador fue actualizado satisfactoriamente',
                    'nombre' => $organizador->getNombre(),
                ]);
            } else {
                $page = $this->renderView('organizador/_form.html.twig', [
                    'form' => $form->createView(),
                    'organizador' => $organizador,
                    'form_id' => 'organizador_edit',
                    'action' => 'Actualizar',
                    'eliminable'=>$eliminable
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('organizador/_new.html.twig', [
            'organizador' => $organizador,
            'title' => 'Editar organizador',
            'action' => 'Actualizar',
            'form_id' => 'organizador_edit',
            'form' => $form->createView(),
            'eliminable'=>$eliminable
        ]);
    }

    /**
     * @Route("/{id}/delete", name="organizador_delete",options={"expose"=true})
     */
    public function delete(Request $request, Organizador $organizador): Response
    {
        if (!$request->isXmlHttpRequest() || false==$this->esEliminable($organizador) || !$this->isCsrfTokenValid('delete'.$organizador->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($organizador);
        $em->flush();
        return $this->json(['mensaje' => 'El organizador fue eliminado satisfactoriamente']);
    }

    /*
     * Funcion que devuelve un booleano indicando si un organizador es eliminable
     */
    private function esEliminable(Organizador $organizador){
        return $this->getDoctrine()->getManager()
             ->getRepository(Evento::class)
             ->findOneByOrganizador($organizador)==null;
    }
}
