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
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('patente/index.html.twig', [
            'patentes' => $patentes,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="patente_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $patente = new Patente();
        $patente->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$patente->getId());
        $form = $this->createForm(PatenteType::class, $patente, ['action' => $this->generateUrl('patente_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($patente->getId());
                $entityManager->persist($patente);
                $entityManager->flush();
                $this->addFlash('success', 'La patente fue registrada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('patente_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('patente/_form.html.twig', array(
                    'form' => $form->createView(),
                    'patente' => $patente,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('patente/_new.html.twig', [
            'patente' => $patente,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'esDirectivo' => $autor->esDirectivo(),
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
            'esDirectivo' => $patente->getId()->getAutor()->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$patente->getId()->getAutor()->getId() || $patente->getId()->getAutor()->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/edit", name="patente_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Patente $patente, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$patente->getId());
        $estado = $patente->getId()->getEstado();
        $form = $this->createForm(PatenteType::class, $patente, ['action' => $this->generateUrl('patente_edit', ['id' => $patente->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($patente->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $patente->getId()->getAutor()->getId() && $estado != $patente->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($patente->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $patente->getId()->getEstadoString() . '" tu patente ' . $patente->getId()->getTitulo());

                $this->addFlash('success', 'La patente fue actualizada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('patente_index', ['id' => $patente->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('patente/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'patente' => $patente,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('patente/_new.html.twig', [
            'patente' => $patente,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar patente',
            'user_id' => $patente->getId()->getAutor()->getId(),
            'esDirectivo' => $patente->getId()->getAutor()->esDirectivo(),
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
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$patente->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$patente->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($patente->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'La patente fue eliminada satisfactoriamente'));
    }
}
