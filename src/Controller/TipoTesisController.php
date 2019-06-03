<?php

namespace App\Controller;

use App\Entity\Tesis;
use App\Entity\TipoTesis;
use App\Form\TipoTesisType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/tipotesis")
 */
class TipoTesisController extends AbstractController
{
    /**
     * @Route("/", name="tipo_tesis_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_tesiss = $this->getDoctrine()->getRepository(TipoTesis::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_tesis/_table.html.twig', [
                'tipo_tesiss' => $tipo_tesiss,
            ]);

        return $this->render('tipo_tesis/index.html.twig', [
            'tipo_tesiss' => $tipo_tesiss,
        ]);
    }

    /**
     * @Route("/new", name="tipo_tesis_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $tipo_tesis = new TipoTesis();
        $form = $this->createForm(TipoTesisType::class, $tipo_tesis, ['action' => $this->generateUrl('tipo_tesis_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_tesis);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de tesis fue registrado satisfactoriamente',
                    'nombre' => $tipo_tesis->getNombre(),
                    'clasificacion' => $tipo_tesis->getClasificacion()->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$tipo_tesis->getId())->getValue(),
                    'id' => $tipo_tesis->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_tesis/_form.html.twig', [
                    'form' => $form->createView(),
                    'tipo_tesis' => $tipo_tesis,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_tesis/_new.html.twig', [
            'tipo_tesis' => $tipo_tesis,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_tesis_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, TipoTesis $tipo_tesis): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(TipoTesisType::class, $tipo_tesis, ['action' => $this->generateUrl('tipo_tesis_edit',['id' => $tipo_tesis->getId()])]);
        $form->handleRequest($request);
        $eliminable=$this->esEliminable($tipo_tesis);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_tesis);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de tesis fue actualizado satisfactoriamente',
                    'nombre' => $tipo_tesis->getNombre(),
                    'clasificacion' => $tipo_tesis->getClasificacion()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_tesis/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_tesis_edit',
                    'action' => 'Actualizar',
                    'tipo_tesis' => $tipo_tesis,
                    'eliminable'=>$eliminable,
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_tesis/_new.html.twig', [
            'tipo_tesis' => $tipo_tesis,
            'eliminable'=>$eliminable,
            'title' => 'Editar tipo de tesis',
            'action' => 'Actualizar',
            'form_id' => 'tipo_tesis_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_tesis_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoTesis $tipo_tesis): Response
    {
        if (!$request->isXmlHttpRequest()  || !$this->isCsrfTokenValid('delete'.$tipo_tesis->getId(), $request->query->get('_token')) || false==$this->esEliminable($tipo_tesis))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_tesis);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de tesis fue eliminado satisfactoriamente']);
    }

    private function esEliminable(TipoTesis $tipoTesis){
        return null==$this->getDoctrine()->getManager()->getRepository(Tesis::class)
                ->findOneByTipoTesis($tipoTesis);
    }
}
