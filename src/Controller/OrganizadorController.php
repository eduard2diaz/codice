<?php

namespace App\Controller;

use App\Entity\Encuentro;
use App\Entity\Organizador;
use App\Form\OrganizadorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $organizador = new Organizador();
        $form = $this->createForm(OrganizadorType::class, $organizador, array('action' => $this->generateUrl('organizador_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($organizador);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El organizador fue registrado satisfactoriamente',
                    'nombre' => $organizador->getNombre(),
                    'id' => $organizador->getId(),
                ));
            } else {
                $page = $this->renderView('organizador/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
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
        $form = $this->createForm(OrganizadorType::class, $organizador, array('action' => $this->generateUrl('organizador_edit',array('id' => $organizador->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organizador);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El organizador fue actualizado satisfactoriamente',
                    'nombre' => $organizador->getNombre(),
                ));
            } else {
                $page = $this->renderView('organizador/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'organizador_edit',
                    'action' => 'Actualizar',
                    'eliminable'=>$this->esEliminable($organizador)
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('organizador/_new.html.twig', [
            'organizador' => $organizador,
            'title' => 'Editar organizador',
            'action' => 'Actualizar',
            'form_id' => 'organizador_edit',
            'form' => $form->createView(),
            'eliminable'=>$this->esEliminable($organizador)
        ]);
    }

    /**
     * @Route("/{id}/delete", name="organizador_delete",options={"expose"=true})
     */
    public function delete(Request $request, Organizador $organizador): Response
    {
        if (!$request->isXmlHttpRequest() || false==$this->esEliminable($organizador))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($organizador);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El organizador fue eliminado satisfactoriamente'));
    }

    private function esEliminable(Organizador $organizador){
        return $this->getDoctrine()->getManager()
             ->getRepository(Encuentro::class)
             ->findOneByOrganizador($organizador)==null;
    }
}
