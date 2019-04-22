<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Articulo;
use App\Form\ArticuloType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articulo")
 */
class ArticuloController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="articulo_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('SELECT l FROM App:Articulo l JOIN l.id p JOIN p.autor a WHERE a.id= :id');
        $consulta->setParameter('id', $autor->getId());
        $articulos = $consulta->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('articulo/_table.html.twig', [
                'articulos' => $articulos,
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('articulo/index.html.twig', [
            'articulos' => $articulos,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="articulo_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $articulo = new Articulo();
        $articulo->getId()->setAutor($autor);
        $this->denyAccessUnlessGranted('NEW',$articulo->getId());
        $form = $this->createForm(ArticuloType::class, $articulo, ['action' => $this->generateUrl('articulo_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($articulo->getId());
                $entityManager->persist($articulo);
                $entityManager->flush();
                $this->addFlash('success', 'El artículo fue registrado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('articulo_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('articulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'articulo' => $articulo,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('articulo/_new.html.twig', [
            'articulo' => $articulo,
            'form' => $form->createView(),
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="articulo_show", methods={"GET"})
     */
    public function show(Articulo $articulo): Response
    {
        return $this->render('articulo/show.html.twig', [
            'articulo' => $articulo,
            'user_id' => $articulo->getId()->getAutor()->getId(),
            'user_foto' => null != $articulo->getId()->getAutor()->getRutaFoto() ? $articulo->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $articulo->getId()->getAutor()->__toString(),
            'user_correo' => $articulo->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="articulo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Articulo $articulo, NotificacionService $notificacionService): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$articulo->getId());
        $estado = $articulo->getId()->getEstado();
        $form = $this->createForm(ArticuloType::class, $articulo, ['action' => $this->generateUrl('articulo_edit', ['id' => $articulo->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($articulo->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $articulo->getId()->getAutor()->getId() && $estado != $articulo->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($articulo->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $articulo->getId()->getEstadoString() . '" tu articulo ' . $articulo->getId()->getTitulo());

                $this->addFlash('success', 'El artículo fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('articulo_index', ['id' => $articulo->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('articulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'articulo' => $articulo,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('articulo/_new.html.twig', [
            'articulo' => $articulo,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar artículo',
            'user_id' => $articulo->getId()->getAutor()->getId(),
            'user_foto' => null != $articulo->getId()->getAutor()->getRutaFoto() ? $articulo->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $articulo->getId()->getAutor()->__toString(),
            'user_correo' => $articulo->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="articulo_delete")
     */
    public function delete(Request $request, Articulo $articulo): Response
    {
        if (!$request->isXmlHttpRequest()  || !$this->isCsrfTokenValid('delete'.$articulo->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$articulo->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($articulo->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El artículo fue eliminado satisfactoriamente'));
    }
}
