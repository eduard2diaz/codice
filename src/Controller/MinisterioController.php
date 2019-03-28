<?php

namespace App\Controller;

use App\Entity\Ministerio;
use App\Form\MinisterioType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Pais;

/**
 * @Route("/ministerio")
 */
class MinisterioController extends AbstractController
{
    /**
     * @Route("/", name="ministerio_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $ministerios = $this->getDoctrine()->getRepository(Ministerio::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('ministerio/_table.html.twig', [
                'ministerios' => $ministerios,
            ]);

        return $this->render('ministerio/index.html.twig', [
            'ministerios' => $ministerios,
        ]);
    }

    /**
     * @Route("/new", name="ministerio_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $ministerio = new Ministerio();
        $form = $this->createForm(MinisterioType::class, $ministerio, array('action' => $this->generateUrl('ministerio_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($ministerio);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El ministerio fue registrado satisfactoriamente',
                    'nombre' => $ministerio->getNombre(),
                    'pais' => $ministerio->getPais()->getNombre(),
                    'id' => $ministerio->getId(),
                ));
            } else {
                $page = $this->renderView('ministerio/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('ministerio/_new.html.twig', [
            'ministerio' => $ministerio,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ministerio_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Ministerio $ministerio): Response
    {
        $form = $this->createForm(MinisterioType::class, $ministerio, array('action' => $this->generateUrl('ministerio_edit',array('id' => $ministerio->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($ministerio);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El ministerio fue actualizado satisfactoriamente',
                    'nombre' => $ministerio->getNombre(),
                    'pais' => $ministerio->getPais()->getNombre(),
                ));
            } else {
                $page = $this->renderView('ministerio/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'ministerio_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('ministerio/_new.html.twig', [
            'ministerio' => $ministerio,
            'title' => 'Editar ministerio',
            'action' => 'Actualizar',
            'form_id' => 'ministerio_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="ministerio_delete",options={"expose"=true})
     */
    public function delete(Request $request, Ministerio $ministerio): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($ministerio);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El ministerio fue eliminado satisfactoriamente'));
    }

    //Funcionalidades ajax

    /**
     * @Route("/{id}/findbypais", name="ministerio_findbypais",options={"expose"=true})
     * Funcioanalidad que retorna el listado de ministerios que pertenecen a un determinado pais(
     * SE UTILIZA EN EL GESTIONAR INSTITUCION)
     */
    public function findbypais(Request $request, Pais $pais): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $ministerios=$em->getRepository(Ministerio::class)->findByPais($pais);

        $ministerios_array=[];
        foreach ($ministerios as $ministerio)
            $ministerios_array[]=['id'=>$ministerio->getId(),'nombre'=>$ministerio->getNombre()];

        return new JsonResponse($ministerios_array);
    }
}
