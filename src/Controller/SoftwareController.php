<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Software;
use App\Entity\Publicacion;
use App\Form\SoftwareType;
use App\Services\NotificacionService;
use App\Tool\Util;
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
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('software/index.html.twig', [
            'softwares' => $softwares,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="software_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $software = new Software();
        $software->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$software->getId());
        $form = $this->createForm(SoftwareType::class, $software, ['action' => $this->generateUrl('software_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($software->getId());
                $entityManager->persist($software);
                $entityManager->flush();
                $this->addFlash('success', 'El software fue registrado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('software_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('software/_form.html.twig', array(
                    'form' => $form->createView(),
                    'software' => $software,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('software/_new.html.twig', [
            'software' => $software,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'esDirectivo' => $autor->esDirectivo(),
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
        Util::generoPublicacion($software->getId()->getChildType());
        return $this->render('software/show.html.twig', [
            'software' => $software,
            'user_id' => $software->getId()->getAutor()->getId(),
            'user_foto' => null != $software->getId()->getAutor()->getRutaFoto() ? $software->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $software->getId()->getAutor()->__toString(),
            'user_correo' => $software->getId()->getAutor()->getEmail(),
            'esDirectivo' => $software->getId()->getAutor()->esDirectivo(),
            'esGestor'=>$this->getUser()->getId()==$software->getId()->getAutor()->getId() || $software->getId()->getAutor()->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/edit", name="software_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Software $software, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$software->getId());
        $estado = $software->getId()->getEstado();
        $form = $this->createForm(SoftwareType::class, $software, ['action' => $this->generateUrl('software_edit', ['id' => $software->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($software->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $software->getId()->getAutor()->getId() && $estado != $software->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($software->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $software->getId()->getEstadoString() . '" tu software ' . $software->getId()->getTitulo());

                $this->addFlash('success', 'El software fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('software_index', ['id' => $software->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('software/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'software' => $software,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('software/_new.html.twig', [
            'software' => $software,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar software',
            'esDirectivo' => $software->getId()->getAutor()->esDirectivo(),
            'user_id' => $software->getId()->getAutor()->getId(),
            'user_foto' => null != $software->getId()->getAutor()->getRutaFoto() ? $software->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $software->getId()->getAutor()->__toString(),
            'user_correo' => $software->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="software_delete")
     */
    public function delete(Request $request, Software $software): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$software->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$software->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($software->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El software fue eliminado satisfactoriamente'));
    }
}
