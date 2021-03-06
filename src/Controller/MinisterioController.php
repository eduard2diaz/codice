<?php

namespace App\Controller;

use App\Entity\Institucion;
use App\Entity\Ministerio;
use App\Form\MinisterioType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $ministerio = new Ministerio();
        $form = $this->createForm(MinisterioType::class, $ministerio, ['action' => $this->generateUrl('ministerio_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($ministerio);
                $em->flush();
                return $this->json(['mensaje' => 'El ministerio fue registrado satisfactoriamente',
                    'nombre' => $ministerio->getNombre(),
                    'pais' => $ministerio->getPais()->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$ministerio->getId())->getValue(),
                    'id' => $ministerio->getId(),
                ]);
            } else {
                $page = $this->renderView('ministerio/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(MinisterioType::class, $ministerio, ['action' => $this->generateUrl('ministerio_edit',['id' => $ministerio->getId()])]);
        $form->handleRequest($request);
        $eliminable=$this->esEliminable($ministerio);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($ministerio);
                $em->flush();
                return $this->json(['mensaje' => 'El ministerio fue actualizado satisfactoriamente',
                    'nombre' => $ministerio->getNombre(),
                    'pais' => $ministerio->getPais()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('ministerio/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'ministerio_edit',
                    'action' => 'Actualizar',
                    'ministerio' => $ministerio,
                    'eliminable' => $eliminable,
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('ministerio/_new.html.twig', [
            'ministerio' => $ministerio,
            'eliminable' => $eliminable,
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
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$ministerio->getId(), $request->query->get('_token')) || false==$this->esEliminable($ministerio))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($ministerio);
        $em->flush();
        return $this->json(['mensaje' => 'El ministerio fue eliminado satisfactoriamente']);
    }

    //Funcionalidades ajax

    /**
     * @Route("/{id}/findbypais", name="ministerio_findbypais",options={"expose"=true})
     * Funcionalidad que retorna el listado de ministerios que pertenecen a un determinado pais(
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

        return $this->json($ministerios_array);
    }

    private function esEliminable(Ministerio $ministerio){
        return null==$this->getDoctrine()->getManager()->getRepository(Institucion::class)
                ->findOneByMinisterio($ministerio);
    }
}
