<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Libro;
use App\Entity\Publicacion;
use App\Form\LibroType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/libro")
 */
class LibroController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="libro_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Libro l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $libros = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('libro/_table.html.twig', [
                'libros' => $libros,
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
            ]);

        return $this->render('libro/index.html.twig', [
            'libros' => $libros,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="libro_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $libro = new Libro();
        $libro->setId(new Publicacion());
        $libro->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$libro->getId());
        $form = $this->createForm(LibroType::class, $libro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($libro->getId());
            $entityManager->persist($libro);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su libro " . $libro->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu libro " . $libro->getId()->getTitulo());

            $this->addFlash('success', 'El libro fue registrado satisfactoriamente');

            return $this->redirectToRoute('libro_index', ['id' => $autor->getId()]);
        }

        return $this->render('libro/new.html.twig', [
            'libro' => $libro,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="libro_show", methods={"GET"})
     */
    public function show(Libro $libro): Response
    {
        return $this->render('libro/show.html.twig', [
            'libro' => $libro,

            'user_id' => $libro->getId()->getAutor()->getId(),
            'user_foto' => null != $libro->getId()->getAutor()->getRutaFoto() ? $libro->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $libro->getId()->getAutor()->__toString(),
            'user_correo' => $libro->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="libro_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Libro $libro, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('NEW',$libro->getId());
        $estado = $libro->getId()->getEstado();
        $form = $this->createForm(LibroType::class, $libro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $libro->getId()->getAutor()->getId() && $estado != $libro->getId()->getEstado())
                $notificacionService->nuevaNotificacion($libro->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $libro->getId()->getEstadoString() . '" tu libro ' . $libro->getId()->getTitulo());

            $this->addFlash('success', 'El libro fue actualizado satisfactoriamente');
            return $this->redirectToRoute('libro_index', [
                'id' => $libro->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('libro/edit.html.twig', [
            'libro' => $libro,
            'form' => $form->createView(),

            'user_id' => $libro->getId()->getAutor()->getId(),
            'user_foto' => null != $libro->getId()->getAutor()->getRutaFoto() ? $libro->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $libro->getId()->getAutor()->__toString(),
            'user_correo' => $libro->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="libro_delete")
     */
    public function delete(Request $request, Libro $libro, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('NEW',$libro->getId());
        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $libro->getId()->getAutor()->getId()) {
            if ($libro->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($libro->getId()->getAutor()->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " eliminó su libro " . $libro->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($libro->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu libro " . $libro->getId()->getTitulo());

        $entityManager->remove($libro);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'El libro fue eliminado satisfactoriamente'));
    }
}
