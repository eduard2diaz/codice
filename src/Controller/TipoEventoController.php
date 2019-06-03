<?php

namespace App\Controller;

use App\Entity\Evento;
use App\Entity\TipoEvento;
use App\Form\TipoEventoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tipoevento")
 */
class TipoEventoController extends AbstractController
{
    /**
     * @Route("/", name="tipo_evento_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_eventos = $this->getDoctrine()->getRepository(TipoEvento::class)->findAll();

        /*
         * Si solicitan este metodo por ajax se refresca solamente el listado de tipos de eventos
         */
        if ($request->isXmlHttpRequest())
            return $this->render('tipo_evento/_table.html.twig', [
                'tipo_eventos' => $tipo_eventos,
            ]);

        return $this->render('tipo_evento/index.html.twig', [
            'tipo_eventos' => $tipo_eventos,
        ]);
    }

    /**
     * @Route("/new", name="tipo_evento_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $tipo_evento = new TipoEvento();
        $form = $this->createForm(TipoEventoType::class, $tipo_evento, ['action' => $this->generateUrl('tipo_evento_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_evento);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de evento fue registrado satisfactoriamente',
                    'nombre' => $tipo_evento->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$tipo_evento->getId())->getValue(),
                    'id' => $tipo_evento->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_evento/_form.html.twig', [
                    'form' => $form->createView(),
                    'tipo_evento' => $tipo_evento,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_evento/_new.html.twig', [
            'tipo_evento' => $tipo_evento,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_evento_edit", methods={"GET","POST"}, options={"expose"=true})
     */
    public function edit(Request $request, TipoEvento $tipo_evento): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $eliminable=$this->esEliminable($tipo_evento);
        $form = $this->createForm(TipoEventoType::class, $tipo_evento, ['action' => $this->generateUrl('tipo_evento_edit',['id' => $tipo_evento->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_evento);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de evento fue actualizado satisfactoriamente',
                    'nombre' => $tipo_evento->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_evento/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_evento_edit',
                    'action' => 'Actualizar',
                    'tipo_evento' => $tipo_evento,
                    'eliminable'=>$this->esEliminable($tipo_evento),
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_evento/_new.html.twig', [
            'tipo_evento' => $tipo_evento,
            'title' => 'Editar tipo de evento',
            'action' => 'Actualizar',
            'form_id' => 'tipo_evento_edit',
            'eliminable'=>$this->esEliminable($tipo_evento),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_evento_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoEvento $tipo_evento): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$tipo_evento->getId(), $request->query->get('_token')) || false==$this->esEliminable($tipo_evento))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_evento);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de evento fue eliminado satisfactoriamente']);
    }

    private function esEliminable(TipoEvento $tipoEvento){
        return null==$this->getDoctrine()->getManager()->getRepository(Evento::class)
                ->findOneByTipoEvento($tipoEvento);
    }
}
