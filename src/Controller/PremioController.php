<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Premio;
use App\Form\PremioType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/premio")
 */
class PremioController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="premio_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Premio l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $premios = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('premio/_table.html.twig', [
                'premios' => $premios,
                'esGestor' => $this->getUser()->getId() == $autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('premio/index.html.twig', [
            'premios' => $premios,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
            'esGestor' => $this->getUser()->getId() == $autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="premio_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $premio = new Premio();
        $premio->getId()->setAutor($autor);
        $this->denyAccessUnlessGranted('NEW', $premio->getId());
        $form = $this->createForm(PremioType::class, $premio, ['action' => $this->generateUrl('premio_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($premio->getId());
                $entityManager->persist($premio);
                $entityManager->flush();
                $this->addFlash('success', 'El premio fue registrado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('premio_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('premio/_form.html.twig', array(
                    'form' => $form->createView(),
                    'premio' => $premio,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('premio/_new.html.twig', [
            'premio' => $premio,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'esDirectivo' => $autor->esDirectivo(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="premio_show", methods={"GET"})
     */
    public function show(Premio $premio): Response
    {
        return $this->render('premio/show.html.twig', [
            'premio' => $premio,
            'user_id' => $premio->getId()->getAutor()->getId(),
            'user_foto' => null != $premio->getId()->getAutor()->getRutaFoto() ? $premio->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $premio->getId()->getAutor()->__toString(),
            'user_correo' => $premio->getId()->getAutor()->getEmail(),
            'esDirectivo' => $premio->getId()->getAutor()->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$premio->getId()->getAutor()->getId() || $premio->getId()->getAutor()->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/edit", name="premio_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Premio $premio, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $premio->getId());
        $estado = $premio->getId()->getEstado();
        $form = $this->createForm(PremioType::class, $premio, ['action' => $this->generateUrl('premio_edit', ['id' => $premio->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($premio->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $premio->getId()->getAutor()->getId() && $estado != $premio->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($premio->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $premio->getId()->getEstadoString() . '" tu premio ' . $premio->getId()->getTitulo());

                $this->addFlash('success', 'El premio fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('premio_index', ['id' => $premio->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('premio/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'premio' => $premio,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('premio/_new.html.twig', [
            'premio' => $premio,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar premio',
            'user_id' => $premio->getId()->getAutor()->getId(),
            'user_foto' => null != $premio->getId()->getAutor()->getRutaFoto() ? $premio->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $premio->getId()->getAutor()->__toString(),
            'user_correo' => $premio->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="premio_delete")
     */
    public function delete(Request $request, Premio $premio): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$premio->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $premio->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($premio->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El premio fue eliminado satisfactoriamente'));
    }
}
