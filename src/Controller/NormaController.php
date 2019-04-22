<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Norma;
use App\Entity\Publicacion;
use App\Form\NormaType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/norma")
 */
class NormaController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="norma_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Norma l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $normas = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('norma/_table.html.twig', [
                'normas' => $normas,
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('norma/index.html.twig', [
            'normas' => $normas,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="norma_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $norma = new Norma();
        $norma->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$norma->getId());
        $form = $this->createForm(NormaType::class, $norma, ['action' => $this->generateUrl('norma_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($norma->getId());
                $entityManager->persist($norma);
                $entityManager->flush();
                $this->addFlash('success', 'La norma fue registrada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('norma_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('norma/_form.html.twig', array(
                    'form' => $form->createView(),
                    'norma' => $norma,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('norma/_new.html.twig', [
            'norma' => $norma,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="norma_show", methods={"GET"})
     */
    public function show(Norma $norma): Response
    {
        return $this->render('norma/show.html.twig', [
            'norma' => $norma,
            'user_id' => $norma->getId()->getAutor()->getId(),
            'user_foto' => null != $norma->getId()->getAutor()->getRutaFoto() ? $norma->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $norma->getId()->getAutor()->__toString(),
            'user_correo' => $norma->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="norma_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Norma $norma, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$norma->getId());
        $estado = $norma->getId()->getEstado();
        $form = $this->createForm(NormaType::class, $norma, ['action' => $this->generateUrl('norma_edit', ['id' => $norma->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($norma->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $norma->getId()->getAutor()->getId() && $estado != $norma->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($norma->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $norma->getId()->getEstadoString() . '" tu norma ' . $norma->getId()->getTitulo());

                $this->addFlash('success', 'La norma fue actualizada satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('norma_index', ['id' => $norma->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('norma/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'norma' => $norma,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('norma/_new.html.twig', [
            'norma' => $norma,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar norma',
            'user_id' => $norma->getId()->getAutor()->getId(),
            'user_foto' => null != $norma->getId()->getAutor()->getRutaFoto() ? $norma->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $norma->getId()->getAutor()->__toString(),
            'user_correo' => $norma->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="norma_delete")
     */
    public function delete(Request $request, Norma $norma, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$norma->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$norma->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($norma->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'La norma fue eliminada satisfactoriamente'));
    }
}
