<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Tesis;
use App\Entity\Publicacion;
use App\Form\TesisType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/tesis")
 */
class TesisController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="tesis_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Tesis l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $tesiss = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('tesis/_table.html.twig', [
                'tesiss' => $tesiss,
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
            ]);

        return $this->render('tesis/index.html.twig', [
            'tesiss' => $tesiss,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="tesis_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $tesis = new Tesis();
        $tesis->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$tesis->getId());
        $form = $this->createForm(TesisType::class, $tesis, ['action' => $this->generateUrl('tesis_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($tesis->getId());
                $entityManager->persist($tesis);
                $entityManager->flush();
                $this->addFlash('success', 'La tesis fue registrada satisfactoriamente');
                return new JsonResponse(['ruta' => $this->generateUrl('tesis_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('tesis/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('tesis/_new.html.twig', [
            'tesis' => $tesis,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="tesis_show", methods={"GET"})
     */
    public function show(Tesis $tesis): Response
    {
        return $this->render('tesis/show.html.twig', [
            'tesis' => $tesis,

            'user_id' => $tesis->getId()->getAutor()->getId(),
            'user_foto' => null != $tesis->getId()->getAutor()->getRutaFoto() ? $tesis->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $tesis->getId()->getAutor()->__toString(),
            'user_correo' => $tesis->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tesis_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tesis $tesis, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$tesis->getId());
        $estado = $tesis->getId()->getEstado();
        $form = $this->createForm(TesisType::class, $tesis, ['action' => $this->generateUrl('tesis_edit', ['id' => $tesis->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($tesis->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $tesis->getId()->getAutor()->getId() && $estado != $tesis->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($tesis->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $tesis->getId()->getEstadoString() . '" tu tesis ' . $tesis->getId()->getTitulo());

                $this->addFlash('success', 'La tesis fue actualizado satisfactoriamente');
                return new JsonResponse(['ruta' => $this->generateUrl('tesis_index', ['id' => $tesis->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('tesis/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('tesis/_new.html.twig', [
            'tesis' => $tesis,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar tesis',

            'user_id' => $tesis->getId()->getAutor()->getId(),
            'user_foto' => null != $tesis->getId()->getAutor()->getRutaFoto() ? $tesis->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $tesis->getId()->getAutor()->__toString(),
            'user_correo' => $tesis->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tesis_delete")
     */
    public function delete(Request $request, Tesis $tesis, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$tesis->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($tesis->getId());
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La tesis fue eliminada satisfactoriamente'));
    }
}
