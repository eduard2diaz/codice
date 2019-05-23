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
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('monografia/index.html.twig', [
            'monografias' => $monografias,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="monografia_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $monografia = new Monografia();
        $monografia->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$monografia->getId());
        $form = $this->createForm(MonografiaType::class, $monografia, ['action' => $this->generateUrl('monografia_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($monografia->getId());
                $entityManager->persist($monografia);
                $entityManager->flush();
                $this->addFlash('success', 'La monografía fue registrada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('monografia_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('monografia/_form.html.twig', array(
                    'form' => $form->createView(),
                    'monografia' => $monografia,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('monografia/_new.html.twig', [
            'monografia' => $monografia,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'esDirectivo' => $autor->esDirectivo(),
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
            'esDirectivo' => $monografia->getId()->getAutor()->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$monografia->getId()->getAutor()->getId() || $monografia->getId()->getAutor()->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/edit", name="monografia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Monografia $monografia, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$monografia->getId());
        $estado = $monografia->getId()->getEstado();
        $form = $this->createForm(MonografiaType::class, $monografia, ['action' => $this->generateUrl('monografia_edit', ['id' => $monografia->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($monografia->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $monografia->getId()->getAutor()->getId() && $estado != $monografia->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($monografia->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $monografia->getId()->getEstadoString() . '" tu monografia ' . $monografia->getId()->getTitulo());

                $this->addFlash('success', 'La monografía fue actualizada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('monografia_index', ['id' => $monografia->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('monografia/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'monografia' => $monografia,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('monografia/_new.html.twig', [
            'monografia' => $monografia,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar monografia',
            'user_id' => $monografia->getId()->getAutor()->getId(),
            'esDirectivo' => $monografia->getId()->getAutor()->esDirectivo(),
            'user_foto' => null != $monografia->getId()->getAutor()->getRutaFoto() ? $monografia->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $monografia->getId()->getAutor()->__toString(),
            'user_correo' => $monografia->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="monografia_delete")
     */
    public function delete(Request $request, Monografia $monografia): Response
    {
        if (!$request->isXmlHttpRequest()|| !$this->isCsrfTokenValid('delete'.$monografia->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$monografia->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($monografia->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'La monografia fue eliminada satisfactoriamente'));
    }
}
