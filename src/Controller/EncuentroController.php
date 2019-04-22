<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Encuentro;
use App\Form\EncuentroType;
use App\Services\NotificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
                'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
            ]);

        return $this->render('encuentro/index.html.twig', [
            'encuentros' => $encuentros,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esGestor'=>$this->getUser()->getId()==$autor->getId() || $autor->esSubordinado($this->getUser())
        ]);
    }

    /**
     * @Route("/{id}/new", name="encuentro_new", methods={"GET","POST"})
     */
    public function new(Request $request, Autor $autor, NotificacionService $notificacionService): Response
    {
        $encuentro = new Encuentro();
        $encuentro->getId()->setAutor($autor);

        $this->denyAccessUnlessGranted('NEW',$encuentro->getId());
        $form = $this->createForm(EncuentroType::class, $encuentro, ['action' => $this->generateUrl('encuentro_new', ['id' => $autor->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($encuentro->getId());
                $entityManager->persist($encuentro);
                $entityManager->flush();
                $this->addFlash('success', 'El encuentro fue registrado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('encuentro_index', ['id' => $autor->getId()])]);
            } else {
                $page = $this->renderView('encuentro/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('encuentro/_new.html.twig', [
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
        $this->denyAccessUnlessGranted('EDIT',$encuentro->getId());
        $estado = $encuentro->getId()->getEstado();
        $form = $this->createForm(EncuentroType::class, $encuentro, ['action' => $this->generateUrl('encuentro_edit', ['id' => $encuentro->getId()->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                if ($encuentro->getId()->getAutor()->getJefe() != null && $this->getUser()->getId() != $encuentro->getId()->getAutor()->getId() && $estado != $encuentro->getId()->getEstado())
                    $notificacionService->nuevaNotificacion($encuentro->getId()->getAutor()->getId(), 'El usuario ' . $this->getUser()->__toString() . ' modificó a "' . $encuentro->getId()->getEstadoString() . '" tu encuentro ' . $encuentro->getId()->getTitulo());

                $this->addFlash('success', 'El encuentro fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('encuentro_index', ['id' => $encuentro->getId()->getAutor()->getId()])]);
            } else {
                $page = $this->renderView('encuentro/_form.html.twig', array(
                    'form' => $form->createView(),
                    'button_action' => 'Actualizar',
                    'encuentro' => $encuentro,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('encuentro/_new.html.twig', [
            'encuentro' => $encuentro,
            'form' => $form->createView(),
            'button_action' => 'Actualizar',
            'form_title' => 'Editar encuentro',
            'user_id' => $encuentro->getId()->getAutor()->getId(),
            'user_foto' => null != $encuentro->getId()->getAutor()->getRutaFoto() ? $encuentro->getId()->getAutor()->getRutaFoto() : null,
            'user_nombre' => $encuentro->getId()->getAutor()->__toString(),
            'user_correo' => $encuentro->getId()->getAutor()->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="encuentro_delete")
     */
    public function delete(Request $request, Encuentro $encuentro): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$encuentro->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE',$encuentro->getId());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($encuentro->getId());
        $entityManager->flush();

        return $this->json(array('mensaje' => 'El encuentro fue eliminado satisfactoriamente'));
    }
}
