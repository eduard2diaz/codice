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
            ]);

        return $this->render('tesis/index.html.twig', [
            'tesiss' => $tesiss,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="tesis_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $tesis = new Tesis();
        $tesis->setId(new Publicacion());
        $tesis->getId()->setAutor($autor);

        $form = $this->createForm(TesisType::class, $tesis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($tesis->getId());
            $entityManager->persist($tesis);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su tesis " . $tesis->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu tesis " . $tesis->getId()->getTitulo());

            $this->addFlash('success', 'La tesis fue registrada satisfactoriamente');

            return $this->redirectToRoute('tesis_index', ['id' => $autor->getId()]);
        }

        return $this->render('tesis/new.html.twig', [
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
        $estado = $tesis->getId()->getEstado();
        $form = $this->createForm(TesisType::class, $tesis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $tesis->getId()->getAutor()->getId() && $estado != $tesis->getId()->getEstado())
                $notificacionService->nuevaNotificacion($tesis->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $tesis->getId()->getEstadoString() . '" tu tesis ' . $tesis->getId()->getTitulo());

            $this->addFlash('success', 'La tesis fue actualizada satisfactoriamente');
            return $this->redirectToRoute('tesis_index', [
                'id' => $tesis->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('tesis/edit.html.twig', [
            'tesis' => $tesis,
            'form' => $form->createView(),

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

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $tesis->getId()->getAutor()->getId()) {
            if ($tesis->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($tesis->getId()->getAutor()->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " eliminó su tesis " . $tesis->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($tesis->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu tesis " . $tesis->getId()->getTitulo());

        $entityManager->remove($tesis);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La tesis fue eliminada satisfactoriamente'));
    }
}
