<?php

namespace App\Controller;

use App\Entity\TipoSoftware;
use App\Form\TipoSoftwareType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tiposoftware")
 */
class TipoSoftwareController extends AbstractController
{
    /**
     * @Route("/", name="tipo_software_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $tipo_softwares = $this->getDoctrine()->getRepository(TipoSoftware::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('tipo_software/_table.html.twig', [
                'tipo_softwares' => $tipo_softwares,
            ]);

        return $this->render('tipo_software/index.html.twig', [
            'tipo_softwares' => $tipo_softwares,
        ]);
    }

    /**
     * @Route("/new", name="tipo_software_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $tipo_software = new TipoSoftware();
        $form = $this->createForm(TipoSoftwareType::class, $tipo_software, ['action' => $this->generateUrl('tipo_software_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($tipo_software);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de software fue registrado satisfactoriamente',
                    'nombre' => $tipo_software->getNombre(),
                    'clasificacion' => $tipo_software->getClasificacion()->getNombre(),
                    'id' => $tipo_software->getId(),
                ]);
            } else {
                $page = $this->renderView('tipo_software/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('tipo_software/_new.html.twig', [
            'tipo_software' => $tipo_software,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tipo_software_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, TipoSoftware $tipo_software): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(TipoSoftwareType::class, $tipo_software, ['action' => $this->generateUrl('tipo_software_edit',['id' => $tipo_software->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($tipo_software);
                $em->flush();
                return $this->json(['mensaje' => 'El tipo de software fue actualizado satisfactoriamente',
                    'nombre' => $tipo_software->getNombre(),
                    'clasificacion' => $tipo_software->getClasificacion()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('tipo_software/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'tipo_software_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('tipo_software/_new.html.twig', [
            'tipo_software' => $tipo_software,
            'title' => 'Editar tipo de software',
            'action' => 'Actualizar',
            'form_id' => 'tipo_software_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tipo_software_delete",options={"expose"=true})
     */
    public function delete(Request $request, TipoSoftware $tipo_software): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($tipo_software);
        $em->flush();
        return $this->json(['mensaje' => 'El tipo de software fue eliminado satisfactoriamente']);
    }
}
