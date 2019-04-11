<?php

namespace App\Controller;

use App\Entity\TipoEncuentro;
use App\Form\TipoEncuentroType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tipoencuentro")
 */
class TipoEncuentroController extends AbstractController
{
    /**
     * @Route("/", name="tipo_encuentro_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_encuentros = $this->getDoctrine()->getRepository(TipoEncuentro::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_encuentro/_table.html.twig', [
                'tipo_encuentros' => $tipo_encuentros,
            ]);

        return $this->render('tipo_encuentro/index.html.twig', [
            'tipo_encuentros' => $tipo_encuentros,
        ]);
    }

    /**
     * @Route("/new", name="tipo_encuentro_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $tipo_encuentro = new TipoEncuentro();
        $form = $this->createForm(TipoEncuentroType::class, $tipo_encuentro, ['action' => $this->generateUrl('tipo_encuentro_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_encuentro);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de encuentro fue registrado satisfactoriamente',
                    'nombre' => $tipo_encuentro->getNombre(),
                    'id' => $tipo_encuentro->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_encuentro/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_encuentro/_new.html.twig', [
            'tipo_encuentro' => $tipo_encuentro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_encuentro_edit", methods={"GET","POST"}, options={"expose"=true})
     */
    public function edit(Request $request, TipoEncuentro $tipo_encuentro): Response
    {
        $form = $this->createForm(TipoEncuentroType::class, $tipo_encuentro, ['action' => $this->generateUrl('tipo_encuentro_edit',['id' => $tipo_encuentro->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_encuentro);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de encuentro fue actualizado satisfactoriamente',
                    'nombre' => $tipo_encuentro->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_encuentro/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_encuentro_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_encuentro/_new.html.twig', [
            'tipo_encuentro' => $tipo_encuentro,
            'title' => 'Editar tipo de encuentro',
            'action' => 'Actualizar',
            'form_id' => 'tipo_encuentro_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_encuentro_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoEncuentro $tipo_encuentro): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_encuentro);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de encuentro fue eliminado satisfactoriamente']);
    }
}
