<?php

namespace App\Controller;

use App\Entity\BalanceAnual;
use App\Form\BalanceAnualType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/balanceanual")
 */
class BalanceAnualController extends AbstractController
{
    /**
     * @Route("/", name="balance_anual_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $balances = $this->getDoctrine()->getRepository(BalanceAnual::class)
            ->findByInstitucion($this->getUser()->getInstitucion());

        if ($request->isXmlHttpRequest())
            return $this->render('balance_anual/_table.html.twig', [
                'balances' => $balances,
            ]);

        return $this->render('balance_anual/index.html.twig', [
            'balances' => $balances,
        ]);
    }

    /**
     * @Route("/new", name="balance_anual_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $balance = new BalanceAnual();
        $balance->setUsuario($this->getUser());
        $balance->setInstitucion($this->getUser()->getInstitucion());
        $form = $this->createForm(BalanceAnualType::class, $balance, array('action' => $this->generateUrl('balance_anual_new')));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($balance);
                $em->flush();
                return $this->json(array('mensaje' => 'El balance fue registrado satisfactoriamente',
                    'nombre' => $balance->getNombre(),
                    'institucion' => $balance->getInstitucion()->getNombre(),
                    'usuario' => $this->getUser()->getNombre(),
                    'id' => $balance->getId(),
                ));
            } else {
                $page = $this->renderView('balance_anual/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('balance_anual/_new.html.twig', [
            'balance' => $balance,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="balance_anual_show", methods={"GET"}, options={"expose"=true})
     */
    public function show(Request $request,BalanceAnual $balanceAnual): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW',$balanceAnual);
        return $this->render('balance_anual/show.html.twig', [
            'balance_anual' => $balanceAnual,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="balance_anual_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, BalanceAnual $balance): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT',$balance);
        $form = $this->createForm(BalanceAnualType::class, $balance, array('action' => $this->generateUrl('balance_anual_edit', ['id' => $balance->getId()])));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($balance);
                $em->flush();
                return $this->json(array('mensaje' => 'El balance fue actualizado satisfactoriamente',
                    'nombre' => $balance->getNombre(),
                    'institucion' => $balance->getInstitucion()->getNombre(),
                    'usuario' => $this->getUser()->getNombre(),
                    'title' => 'Editar balance',
                    'action' => 'Actualizar',
                    'form_id' => 'balance_edit'
                ));
            } else {
                $page = $this->renderView('balance_anual/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('balance_anual/_new.html.twig', [
            'balance' => $balance,
            'form' => $form->createView(),
            'title' => 'Editar balance',
            'action' => 'Actualizar',
            'form_id' => 'balance_edit'
        ]);
    }

    /**
     * @Route("/{id}/delete", name="balance_anual_delete", options={"expose"=true})
     */
    public function delete(Request $request, BalanceAnual $balanceAnual): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$balanceAnual);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($balanceAnual);
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El balance fue eliminado satisfactoriamente'));
    }

    /**
     * @Route("/{id}/descargar", name="balance_anual_descargar", methods={"GET"})
     */
    public function descargar(BalanceAnual $balanceAnual): Response
    {
        $this->denyAccessUnlessGranted('DOWNLOAD',$balanceAnual);
        $ruta = $this->getParameter('storage_directory') . DIRECTORY_SEPARATOR . $balanceAnual->getRutaArchivo();

        if (!file_exists($ruta))
            throw $this->createNotFoundException();

        $archivo = file_get_contents($ruta);
        return new Response($archivo, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Transfer-Encoding' => 'binary',
            'Content-length' => strlen($archivo),
            'Pragma' => 'no-cache',
            'Expires' => '0'));
    }
}
