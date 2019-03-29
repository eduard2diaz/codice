<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Software;
use App\Entity\Publicacion;
use App\Form\SoftwareType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/software")
 */
class SoftwareController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="software_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Software l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $softwares = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('software/_table.html.twig', [
                'softwares' => $softwares,
            ]);

        return $this->render('software/index.html.twig', [
            'softwares' => $softwares,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="software_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $software = new Software();
        $software->setId(new Publicacion());
        $software->getId()->setAutor($autor);

        $form = $this->createForm(SoftwareType::class, $software);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($software->getId());
            $entityManager->persist($software);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su software " . $software->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu software " . $software->getId()->getTitulo());

            $this->addFlash('success', 'El software fue registrado satisfactoriamente');

            return $this->redirectToRoute('software_index', ['id' => $autor->getId()]);
        }

        return $this->render('software/new.html.twig', [
            'software' => $software,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="software_show", methods={"GET"})
     */
    public function show(Software $software): Response
    {
        return $this->render('software/show.html.twig', [
            'software' => $software,

            'user_id' => $software->getId()->getAutor()->getId(),
            'user_foto' => null != $software->getId()->getAutor()->getRutaFoto() ? $software->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $software->getId()->getAutor()->__toString(),
            'user_correo' => $software->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="software_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Software $software, NotificacionService $notificacionService): Response
    {
        $estado = $software->getId()->getEstado();
        $form = $this->createForm(SoftwareType::class, $software);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $software->getId()->getAutor()->getId() && $estado != $software->getId()->getEstado())
                $notificacionService->nuevaNotificacion($software->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $software->getId()->getEstadoString() . '" tu software ' . $software->getId()->getTitulo());

            $this->addFlash('success', 'El software fue actualizado satisfactoriamente');
            return $this->redirectToRoute('software_index', [
                'id' => $software->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('software/edit.html.twig', [
            'software' => $software,
            'form' => $form->createView(),

            'user_id' => $software->getId()->getAutor()->getId(),
            'user_foto' => null != $software->getId()->getAutor()->getRutaFoto() ? $software->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $software->getId()->getAutor()->__toString(),
            'user_correo' => $software->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="software_delete")
     */
    public function delete(Request $request, Software $software, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $software->getId()->getAutor()->getId()) {
            if ($software->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($software->getId()->getAutor()->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " eliminó su software " . $software->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($software->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu software " . $software->getId()->getTitulo());

        $entityManager->remove($software);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'El software fue eliminado satisfactoriamente'));
    }
}
