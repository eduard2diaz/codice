<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Monografia;
use App\Entity\Publicacion;
use App\Form\MonografiaType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/monografia")
 */
class MonografiaController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="monografia_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Monografia l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $monografias = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('monografia/_table.html.twig', [
                'monografias' => $monografias,
            ]);

        return $this->render('monografia/index.html.twig', [
            'monografias' => $monografias,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="monografia_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $monografia = new Monografia();
        $monografia->setId(new Publicacion());
        $monografia->getId()->setAutor($autor);

        $form = $this->createForm(MonografiaType::class, $monografia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($monografia->getId());
            $entityManager->persist($monografia);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su monografia " . $monografia->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu monografia " . $monografia->getId()->getTitulo());

            $this->addFlash('success', 'La monografia fue registrada satisfactoriamente');

            return $this->redirectToRoute('monografia_index', ['id' => $autor->getId()]);
        }

        return $this->render('monografia/new.html.twig', [
            'monografia' => $monografia,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="monografia_show", methods={"GET"})
     */
    public function show(Monografia $monografia): Response
    {
        return $this->render('monografia/show.html.twig', [
            'monografia' => $monografia,

            'user_id' => $monografia->getId()->getAutor()->getId(),
            'user_foto' => null != $monografia->getId()->getAutor()->getRutaFoto() ? $monografia->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $monografia->getId()->getAutor()->__toString(),
            'user_correo' => $monografia->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="monografia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Monografia $monografia, NotificacionService $notificacionService): Response
    {
        $estado = $monografia->getId()->getEstado();
        $form = $this->createForm(MonografiaType::class, $monografia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $monografia->getId()->getAutor()->getId() && $estado != $monografia->getId()->getEstado())
                $notificacionService->nuevaNotificacion($monografia->getId()->getAutor()->getId(), 'La usuario ' . $this->getUser()->__toString() . ' modificó a "' . $monografia->getId()->getEstadoString() . '" tu monografia ' . $monografia->getId()->getTitulo());

            $this->addFlash('success', 'La monografia fue actualizada satisfactoriamente');
            return $this->redirectToRoute('monografia_index', [
                'id' => $monografia->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('monografia/edit.html.twig', [
            'monografia' => $monografia,
            'form' => $form->createView(),

            'user_id' => $monografia->getId()->getAutor()->getId(),
            'user_foto' => null != $monografia->getId()->getAutor()->getRutaFoto() ? $monografia->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $monografia->getId()->getAutor()->__toString(),
            'user_correo' => $monografia->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="monografia_delete")
     */
    public function delete(Request $request, Monografia $monografia, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $monografia->getId()->getAutor()->getId()) {
            if ($monografia->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($monografia->getId()->getAutor()->getJefe()->getId(), "La usuario " . $this->getUser()->__toString() . " eliminó su monografia " . $monografia->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($monografia->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu monografia " . $monografia->getId()->getTitulo());

        $entityManager->remove($monografia);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La monografia fue eliminada satisfactoriamente'));
    }
}
