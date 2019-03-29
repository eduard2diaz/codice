<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Encuentro;
use App\Entity\Publicacion;
use App\Form\EncuentroType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/encuentro")
 */
class EncuentroController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="encuentro_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Encuentro l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $encuentros = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('encuentro/_table.html.twig', [
                'encuentros' => $encuentros,
            ]);

        return $this->render('encuentro/index.html.twig', [
            'encuentros' => $encuentros,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="encuentro_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $encuentro = new Encuentro();
        $encuentro->setId(new Publicacion());
        $encuentro->getId()->setAutor($autor);

        $form = $this->createForm(EncuentroType::class, $encuentro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($encuentro->getId());
            $entityManager->persist($encuentro);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su encuentro " . $encuentro->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu encuentro " . $encuentro->getId()->getTitulo());

            $this->addFlash('success', 'La encuentro fue registrada satisfactoriamente');

            return $this->redirectToRoute('encuentro_index', ['id' => $autor->getId()]);
        }

        return $this->render('encuentro/new.html.twig', [
            'encuentro' => $encuentro,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="encuentro_show", methods={"GET"})
     */
    public function show(Encuentro $encuentro): Response
    {
        return $this->render('encuentro/show.html.twig', [
            'encuentro' => $encuentro,

            'user_id' => $encuentro->getId()->getAutor()->getId(),
            'user_foto' => null != $encuentro->getId()->getAutor()->getRutaFoto() ? $encuentro->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $encuentro->getId()->getAutor()->__toString(),
            'user_correo' => $encuentro->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="encuentro_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Encuentro $encuentro, NotificacionService $notificacionService): Response
    {
        $estado = $encuentro->getId()->getEstado();
        $form = $this->createForm(EncuentroType::class, $encuentro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $encuentro->getId()->getAutor()->getId() && $estado != $encuentro->getId()->getEstado())
                $notificacionService->nuevaNotificacion($encuentro->getId()->getAutor()->getId(), 'La usuario ' . $this->getUser()->__toString() . ' modificó a "' . $encuentro->getId()->getEstadoString() . '" tu encuentro ' . $encuentro->getId()->getTitulo());

            $this->addFlash('success', 'La encuentro fue actualizada satisfactoriamente');
            return $this->redirectToRoute('encuentro_index', [
                'id' => $encuentro->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('encuentro/edit.html.twig', [
            'encuentro' => $encuentro,
            'form' => $form->createView(),

            'user_id' => $encuentro->getId()->getAutor()->getId(),
            'user_foto' => null != $encuentro->getId()->getAutor()->getRutaFoto() ? $encuentro->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $encuentro->getId()->getAutor()->__toString(),
            'user_correo' => $encuentro->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="encuentro_delete")
     */
    public function delete(Request $request, Encuentro $encuentro, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $encuentro->getId()->getAutor()->getId()) {
            if ($encuentro->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($encuentro->getId()->getAutor()->getJefe()->getId(), "La usuario " . $this->getUser()->__toString() . " eliminó su encuentro " . $encuentro->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($encuentro->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu encuentro " . $encuentro->getId()->getTitulo());

        $entityManager->remove($encuentro);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La encuentro fue eliminada satisfactoriamente'));
    }
}
