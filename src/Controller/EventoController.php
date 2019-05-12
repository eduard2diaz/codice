<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Evento;
use App\Form\EventoType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/evento")
 */
class EventoController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="evento_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Evento l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $eventos = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('evento/_table.html.twig', [
                'eventos' => $eventos,
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('evento/index.html.twig', [
            'eventos' => $eventos,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="evento_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $evento = new Evento();
        $evento->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$evento->getId());
        $form = $this->createForm(EventoType::class, $evento, ['action' => $this->generateUrl('evento_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($evento->getId());
                $entityManager->persist($evento);
                $entityManager->flush();
                $this->addFlash('success', 'El evento fue registrado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('evento_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('evento/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('evento/_new.html.twig', [
            'evento' => $evento,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'esDirectivo' => $autor->esDirectivo(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="evento_show", methods={"GET"})
     */
    public function show(Evento $evento): Response
    {
        return $this->render('evento/show.html.twig', [
            'evento' => $evento,
            'user_id' => $evento->getId()->getAutor()->getId(),
            'user_foto' => null != $evento->getId()->getAutor()->getRutaFoto() ? $evento->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $evento->getId()->getAutor()->__toString(),
            'user_correo' => $evento->getId()->getAutor()->getEmail(),
            'esDirectivo' => $evento->getId()->getAutor()->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$evento->getId()->getAutor()->getId() || $evento->getId()->getAutor()->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/edit", name="evento_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Evento $evento, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$evento->getId());
        $estado = $evento->getId()->getEstado();
        $form = $this->createForm(EventoType::class, $evento, ['action' => $this->generateUrl('evento_edit', ['id' => $evento->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($evento->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $evento->getId()->getAutor()->getId() && $estado != $evento->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($evento->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $evento->getId()->getEstadoString() . '" tu evento ' . $evento->getId()->getTitulo());

                $this->addFlash('success', 'El evento fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('evento_index', ['id' => $evento->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('evento/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'evento' => $evento,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('evento/_new.html.twig', [
            'evento' => $evento,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar evento',
            'esDirectivo' => $evento->getId()->getAutor()->esDirectivo(),
            'user_id' => $evento->getId()->getAutor()->getId(),
            'user_foto' => null != $evento->getId()->getAutor()->getRutaFoto() ? $evento->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $evento->getId()->getAutor()->__toString(),
            'user_correo' => $evento->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="evento_delete")
     */
    public function delete(Request $request, Evento $evento): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$evento->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$evento->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($evento->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El evento fue eliminado satisfactoriamente'));
    }
}
