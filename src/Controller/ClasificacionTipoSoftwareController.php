<?php

namespace App\Controller;

use App\Entity\ClasificacionTipoSoftware;
use App\Entity\TipoSoftware;
use App\Form\ClasificacionTipoSoftwareType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clasificaciontiposoftware")
 */
class ClasificacionTipoSoftwareController extends AbstractController
{
    /**
     * @Route("/", name="clasificacion_tiposoftware_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $clasificacion_tiposoftware = $this->getDoctrine()->getRepository(ClasificacionTipoSoftware::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('clasificacion_tiposoftware/_table.html.twig', [
                'clasificacion_tiposoftwares' => $clasificacion_tiposoftware,
            ]);

        return $this->render('clasificacion_tiposoftware/index.html.twig', [
            'clasificacion_tiposoftwares' => $clasificacion_tiposoftware,
        ]);
    }

    /**
     * @Route("/new", name="clasificacion_tiposoftware_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $clasificacion_tiposoftware = new ClasificacionTipoSoftware();
        $form = $this->createForm(ClasificacionTipoSoftwareType::class, $clasificacion_tiposoftware, ['action' => $this->generateUrl('clasificacion_tiposoftware_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($clasificacion_tiposoftware);
                $em->flush();
                return $this->json(['mensaje' => 'La clasificación fue registrada satisfactoriamente',
                    'nombre' => $clasificacion_tiposoftware->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$clasificacion_tiposoftware->getId())->getValue(),
                    'id' => $clasificacion_tiposoftware->getId(),
                ]);
            } else {
                $page = $this->renderView('clasificacion_tiposoftware/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('clasificacion_tiposoftware/_new.html.twig', [
            'clasificacion_tiposoftware' => $clasificacion_tiposoftware,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="clasificacion_tiposoftware_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, ClasificacionTipoSoftware $clasificacion_tiposoftware): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $eliminable=$this->esEliminable($clasificacion_tiposoftware);
        $form = $this->createForm(ClasificacionTipoSoftwareType::class, $clasificacion_tiposoftware, ['action' => $this->generateUrl('clasificacion_tiposoftware_edit',['id' => $clasificacion_tiposoftware->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($clasificacion_tiposoftware);
                $em->flush();
                return $this->json(['mensaje' => 'La clasificación fue actualizada satisfactoriamente',
                    'nombre' => $clasificacion_tiposoftware->getNombre(),
                ]);
            } else {
                $page = $this->renderView('clasificacion_tiposoftware/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'clasificacion_tiposoftware_edit',
                    'clasificacion_tiposoftware' => $clasificacion_tiposoftware,
                    'eliminable'=>$eliminable,
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('clasificacion_tiposoftware/_new.html.twig', [
            'clasificacion_tiposoftware' => $clasificacion_tiposoftware,
            'eliminable'=>$eliminable,
            'title' => 'Editar clasificación',
            'action' => 'Actualizar',
            'form_id' => 'clasificacion_tiposoftware_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="clasificacion_tiposoftware_delete",options={"expose"=true})
     */
    public function delete(Request $request, ClasificacionTipoSoftware $clasificacion_tiposoftware): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$clasificacion_tiposoftware->getId(), $request->query->get('_token')) || false==$this->esEliminable($clasificacion_tiposoftware))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($clasificacion_tiposoftware);
        $em->flush();
        return $this->json(['mensaje' => 'La clasificación fue eliminada satisfactoriamente']);
    }

    private function esEliminable(ClasificacionTipoSoftware $clasificacionTipoSoftware){
    return null==$this->getDoctrine()->getManager()->getRepository(TipoSoftware::class)
            ->findOneByClasificacion($clasificacionTipoSoftware);
}
}
