<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Premio;
use App\Entity\Publicacion;
use App\Form\PremioType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            ]);

        return $this->render('premio/index.html.twig', [
            'premios' => $premios,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="premio_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $premio = new Premio();
        $premio->setId(new Publicacion());
        $premio->getId()->setAutor($autor);

        $form = $this->createForm(PremioType::class, $premio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($premio->getId());
            $entityManager->persist($premio);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su premio " . $premio->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu premio " . $premio->getId()->getTitulo());

            $this->addFlash('success', 'El premio fue registrado satisfactoriamente');

            return $this->redirectToRoute('premio_index', ['id' => $autor->getId()]);
        }

        return $this->render('premio/new.html.twig', [
            'premio' => $premio,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
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
        ]);
    }

    /**
     * @Route("/{id}/edit", name="premio_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Premio $premio, NotificacionService $notificacionService): Response
    {
        $estado = $premio->getId()->getEstado();
        $form = $this->createForm(PremioType::class, $premio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $premio->getId()->getAutor()->getId() && $estado != $premio->getId()->getEstado())
                $notificacionService->nuevaNotificacion($premio->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $premio->getId()->getEstadoString() . '" tu premio ' . $premio->getId()->getTitulo());

            $this->addFlash('success', 'El premio fue actualizado satisfactoriamente');
            return $this->redirectToRoute('premio_index', [
                'id' => $premio->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('premio/edit.html.twig', [
            'premio' => $premio,
            'form' => $form->createView(),

            'user_id' => $premio->getId()->getAutor()->getId(),
            'user_foto' => null != $premio->getId()->getAutor()->getRutaFoto() ? $premio->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $premio->getId()->getAutor()->__toString(),
            'user_correo' => $premio->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="premio_delete")
     */
    public function delete(Request $request, Premio $premio, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $premio->getId()->getAutor()->getId()) {
            if ($premio->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($premio->getId()->getAutor()->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " eliminó su premio " . $premio->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($premio->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu premio " . $premio->getId()->getTitulo());

        $entityManager->remove($premio);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'El premio fue eliminado satisfactoriamente'));
    }
}
