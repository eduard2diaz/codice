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
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
            ]);

        return $this->render('norma/index.html.twig', [
            'normas' => $normas,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esJefe($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="norma_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $norma = new Norma();
        $norma->setId(new Publicacion());
        $norma->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$norma->getId());
        $form = $this->createForm(NormaType::class, $norma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($norma->getId());
            $entityManager->persist($norma);
            $entityManager->flush();

            if ($this->getUser()->getId() == $autor->getId()) {
                if ($autor->getJefe() != null)
                    $notificacionService->nuevaNotificacion($autor->getJefe()->getId(), "El usuario " . $this->getUser()->__toString() . " publicó su norma " . $norma->getId()->getTitulo());
            } else
                $notificacionService->nuevaNotificacion($autor->getId(), "El usuario " . $this->getUser()->__toString() . " ha publicado tu norma " . $norma->getId()->getTitulo());

            $this->addFlash('success', 'La norma fue registrada satisfactoriamente');

            return $this->redirectToRoute('norma_index', ['id' => $autor->getId()]);
        }

        return $this->render('norma/new.html.twig', [
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
        $form = $this->createForm(NormaType::class, $norma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($this->getUser()->getId() != $norma->getId()->getAutor()->getId() && $estado != $norma->getId()->getEstado())
                $notificacionService->nuevaNotificacion($norma->getId()->getAutor()->getId(), 'La usuario ' . $this->getUser()->__toString() . ' modificó a "' . $norma->getId()->getEstadoString() . '" tu norma ' . $norma->getId()->getTitulo());

            $this->addFlash('success', 'La norma fue actualizada satisfactoriamente');
            return $this->redirectToRoute('norma_index', [
                'id' => $norma->getId()->getAutor()->getId(),
            ]);
        }

        return $this->render('norma/edit.html.twig', [
            'norma' => $norma,
            'form' => $form->createView(),

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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$norma->getId());
        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getUser()->getId() == $norma->getId()->getAutor()->getId()) {
            if ($norma->getId()->getAutor()->getJefe() != null)
                $notificacionService->nuevaNotificacion($norma->getId()->getAutor()->getJefe()->getId(), "La usuario " . $this->getUser()->__toString() . " eliminó su norma " . $norma->getId()->getTitulo());
        } else
            $notificacionService->nuevaNotificacion($norma->getId()->getAutor()->getId(), "El usuario " . $this->getUser()->__toString() . " ha eliminado tu norma " . $norma->getId()->getTitulo());

        $entityManager->remove($norma);
        $entityManager->flush();

        return new JsonResponse(array('mensaje' => 'La norma fue eliminada satisfactoriamente'));
    }
}
