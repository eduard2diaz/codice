<?php

namespace App\Controller;

use App\Form\NotificacionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Notificacion;

/**
 * @Route("/notificacion")
 */
class NotificacionController extends AbstractController
{

    /**
     * @Route("/", name="notificacion_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->query->get('_format') == 'json') {

                if (null == $this->getUser()->getUltimologout()) {
                    $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()->getId()], ['fecha' => 'DESC'], 5);
                } else {
                    $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT n FROM App:Notificacion n JOIN n.destinatario u WHERE u.id= :usuario AND n.fecha>= :fecha');
                    $consulta->setParameters(['usuario' => $this->getUser()->getId(), 'fecha' => $this->getUser()->getUltimologout()]);
                    $consulta->setMaxResults(5);
                    $notificacions = $consulta->getResult();
                }

                return $this->json([
                    'contador' => count($notificacions),
                    'html' => $this->renderView('notificacion/ajax/_json.html.twig', ['notificaciones' => $notificacions])
                ]);
            }

            $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()], ['fecha' => 'DESC']);
            return $this->render('notificacion/_table.html.twig', [
                'notificacions' => $notificacions,
            ]);

        }

        $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()], ['fecha' => 'DESC']);
        return $this->render('notificacion/index.html.twig', [
            'notificacions' => $notificacions,

            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->__toString(),
            'user_correo' => $this->getUser()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="notificacion_show", methods="GET")
     */
    public function show(Request $request, Notificacion $notificacion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$notificacion);
        return $this->render('notificacion/_show.html.twig', [
            'notificacion' => $notificacion,
        ]);
    }

    /**
     * @Route("/{id}/delete",name="notificacion_delete")
     */
    public function delete(Request $request, Notificacion $notificacion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$notificacion);
        $em = $this->getDoctrine()->getManager();
        $em->remove($notificacion);
        $em->flush();
        return $this->json(array('mensaje' => 'La notificación fue eliminada satisfactoriamente'));


    }

}
