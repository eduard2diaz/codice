<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Patente;
use App\Entity\Publicacion;
use App\Form\PatenteType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/patente")
 */
class PatenteController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="patente_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Patente l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $patentes = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('patente/_table.html.twig', [
                'patentes' => $patentes,
            ]);

        return $this->render('patente/index.html.twig', [
            'patentes' => $patentes,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="patente_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $patente = new Patente();
        $patente->setId(new Publicacion());
        $patente->getId()->setAutor($autor);

        $form = $this->createForm(PatenteType::class, $patente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($patente->getId());
            $entityManager->persist($patente);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su patente " . $patente->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu patente " . $patente->getId()->getTitulo());

            $this->addFlash('success', 'La patente fue registrada satisfactoriamente');

            return $this->redirectToRoute('patente_index', ['id' => $autor->getId()]);
        }

        return $this->render('patente/new.html.twig', [
            'patente' => $patente,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="patente_show", methods={"GET"})
     */
    public function show(Patente $patente): Response
    {
        return $this->render('patente/show.html.twig', [
            'patente' => $patente,

            'user_id' => $patente->getId()->getAutor()->getId(),
            'user_foto' => null != $patente->getId()->getAutor()->getRutaFoto() ? $patente->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $patente->getId()->getAutor()->__toString(),
            'user_correo' => $patente->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="patente_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Patente $patente, NotificacionService $notificacionService): Response
    {
        $estado = $patente->getId()->getEstado();
        $form = $this->createForm(PatenteType::class, $patente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $patente->getId()->getAutor()->getId() && $estado != $patente->getId()->getEstado())
                $notificacionService->nuevaNotificacion($patente->getId()->getAutor()->getId(), 'La usuario ' . $this->getUser()->__toString() . ' modificó a "' . $patente->getId()->getEstadoString() . '" tu patente ' . $patente->getId()->getTitulo());

            $this->addFlash('success', 'La patente fue actualizada satisfactoriamente');
            return $this->redirectToRoute('patente_index', [
                'id' => $patente->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('patente/edit.html.twig', [
            'patente' => $patente,
            'form' => $form->createView(),

            'user_id' => $patente->getId()->getAutor()->getId(),
            'user_foto' => null != $patente->getId()->getAutor()->getRutaFoto() ? $patente->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $patente->getId()->getAutor()->__toString(),
            'user_correo' => $patente->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="patente_delete")
     */
    public function delete(Request $request, Patente $patente, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $patente->getId()->getAutor()->getId()) {
            if ($patente->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($patente->getId()->getAutor()->getJefe()->getId(), "La usuario " . $this->getUser()->__toString() . " eliminó su patente " . $patente->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($patente->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu patente " . $patente->getId()->getTitulo());

        $entityManager->remove($patente);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La patente fue eliminada satisfactoriamente'));
    }
}
