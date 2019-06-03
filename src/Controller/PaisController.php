<?php

namespace App\Controller;

use App\Entity\Editorial;
use App\Entity\Ministerio;
use App\Entity\Pais;
use App\Entity\Revista;
use App\Form\PaisType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pais")
 */
class PaisController extends AbstractController
{
    /**
     * @Route("/", name="pais_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $paiss = $this->getDoctrine()->getRepository(Pais::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('pais/_table.html.twig', [
                'paiss' => $paiss,
            ]);

        return $this->render('pais/index.html.twig', [
            'paiss' => $paiss,
        ]);
    }

    /**
     * @Route("/new", name="pais_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $pais = new Pais();
        $form = $this->createForm(PaisType::class, $pais, ['action' => $this->generateUrl('pais_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($pais);
                $em->flush();
                return $this->json(['mensaje' => 'El país fue registrado satisfactoriamente',
                    'nombre' => $pais->getNombre(),
                    'capital' => $pais->getCapital(),
                    'codigo' => $pais->getCodigo(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$pais->getId())->getValue(),
                    'id' => $pais->getId(),
                ]);
            } else {
                $page = $this->renderView('pais/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('pais/_new.html.twig', [
            'pais' => $pais,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pais_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Pais $pais): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(PaisType::class, $pais, ['action' => $this->generateUrl('pais_edit',['id' => $pais->getId()])]);
        $form->handleRequest($request);

        $eliminable=$this->esEliminable($pais);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($pais);
                $em->flush();
                return $this->json(['mensaje' => 'El país fue actualizado satisfactoriamente',
                    'nombre' => $pais->getNombre(),
                    'capital' => $pais->getCapital(),
                    'codigo' => $pais->getCodigo(),
                ]);
            } else {
                $page = $this->renderView('pais/_form.html.twig', [
                    'form' => $form->createView(),
                    'form_id' => 'pais_edit',
                    'action' => 'Actualizar',
                    'pais' => $pais,
                    'eliminable' => $eliminable,
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('pais/_new.html.twig', [
            'pais' => $pais,
            'eliminable' => $eliminable,
            'title' => 'Editar país',
            'action' => 'Actualizar',
            'form_id' => 'pais_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="pais_delete",options={"expose"=true})
     */
    public function delete(Request $request, Pais $pais): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$pais->getId(), $request->query->get('_token')) || false==$this->esEliminable($pais))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($pais);
        $em->flush();
        return $this->json(['mensaje' => 'El país fue eliminado satisfactoriamente']);
    }

    private function esEliminable(Pais $pais)
    {
        $em = $this->getDoctrine()->getManager();
        $entidades = [
            ['nombre' => Ministerio::class, 'foranea' => 'pais'],
            ['nombre' => Editorial::class, 'foranea' => 'pais'],
            ['nombre' => Revista::class, 'foranea' => 'pais'],
        ];

        foreach ($entidades as $value) {
            $result = $em->getRepository($value['nombre'])->findOneBy([$value['foranea'] => $pais]);
            if(null!=$result)
                return false;
        }
        return true;
    }
}
