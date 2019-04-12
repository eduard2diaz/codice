<?php

namespace App\Controller;

use App\Entity\Articulo;
use App\Entity\Revista;
use App\Form\RevistaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/revista")
 */
class RevistaController extends AbstractController
{
    /**
     * @Route("/", name="revista_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $revistas = $this->getDoctrine()->getRepository(Revista::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('revista/_table.html.twig', [
                'revistas' => $revistas,
            ]);

        return $this->render('revista/index.html.twig', [
            'revistas' => $revistas,
        ]);
    }

    /**
     * @Route("/new", name="revista_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();
        
        $revista = new Revista();
        $form = $this->createForm(RevistaType::class, $revista, ['action' => $this->generateUrl('revista_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($revista);
                $em->flush();
                return $this->json(['mensaje' => 'La revista fue registrada satisfactoriamente',
                    'nombre' => $revista->getNombre(),
                    'nivel' => $revista->getNivel(),
                    'impacto' => $revista->getImpacto(),
                    'pais' => $revista->getPais()->getNombre(),
                    'id' => $revista->getId(),
                ]);
            } else {
                $page = $this->renderView('revista/_form.html.twig', [
                    'form' => $form->createView(),
                    'revista' => $revista,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('revista/_new.html.twig', [
            'revista' => $revista,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="revista_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Revista $revista): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();
        
        $form = $this->createForm(RevistaType::class, $revista, ['action' => $this->generateUrl('revista_edit',['id' => $revista->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($revista);
                $em->flush();
                return $this->json(['mensaje' => 'La revista fue actualizada satisfactoriamente',
                    'nombre' => $revista->getNombre(),
                    'nivel' => $revista->getNivel(),
                    'impacto' => $revista->getImpacto(),
                    'pais' => $revista->getPais()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('revista/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'revista_edit',
                    'action' => 'Actualizar',
                    'revista' => $revista,
                    'eliminable'=>$this->esEliminable($revista)
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('revista/_new.html.twig', [
            'revista' => $revista,
            'title' => 'Editar revista',
            'action' => 'Actualizar',
            'form_id' => 'revista_edit',
            'form' => $form->createView(),
            'eliminable'=>$this->esEliminable($revista)
        ]);
    }

    /**
     * @Route("/{id}/delete", name="revista_delete",options={"expose"=true})
     */
    public function delete(Request $request, Revista $revista): Response
    {
        if (!$request->isXmlHttpRequest() || false==$this->esEliminable($revista))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($revista);
        $em->flush();
        return $this->json(['mensaje' => 'La revista fue eliminada satisfactoriamente']);
    }

    /*
     *Funcion que devuelve un booleano indicando si una revista es o no eliminable
     */
    private function esEliminable(Revista $revista){
        return $this->getDoctrine()->getManager()
                ->getRepository(Articulo::class)
                ->findOneByRevista($revista)==null;
    }
}
