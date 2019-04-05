<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/usuario")
 */
class UsuarioController extends AbstractController
{
    /**
     * @Route("/", name="usuario_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $usuarios = $this->getDoctrine()->getManager()->getRepository(Usuario::class)->findAll();
        if ($request->isXmlHttpRequest())
            return $this->render('usuario/_table.html.twig', ['usuarios' => $usuarios,]);

        return $this->render('usuario/index.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * @Route("/new", name="usuario_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario, ['action' => $this->generateUrl('usuario_new')]);
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($usuario);
                $entityManager->flush();
                return new JsonResponse([
                    'mensaje' => 'El administrador fue registrado satisfactoriamente',
                    'nombre' => $usuario->getNombre(),
                    'correo' => $usuario->getEmail(),
                    'activo' => $usuario->getActivo(),
                    'id' => $usuario->getId()
                ]);
            } else {
                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                    'usuario' => $usuario,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('usuario/_new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="usuario_show", methods={"GET"},options={"expose"=true})
     */
    public function show(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('usuario/_show.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Usuario $usuario, UserPasswordEncoderInterface $encoder): Response
    {
        if (!$request->isXmlHttpRequest())
          throw $this->createAccessDeniedException();

        $passwordOriginal = $usuario->getPassword();
        $form = $this->createForm(UsuarioType::class, $usuario, ['action' => $this->generateUrl('usuario_edit', ['id' => $usuario->getId()])]);
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');
                if (null == $usuario->getPassword()) {
                    $usuario->setPassword($passwordOriginal);
                } else
                    $usuario->setPassword($encoder->encodePassword($usuario, $usuario->getPassword()));

                if ($usuario->getFile() != null) {
                    if ($usuario->getRutaFoto() != null)
                        $usuario->actualizarFoto($ruta);
                    else
                        $usuario->Upload($ruta);
                    $usuario->setFile(null);
                }

                $this->getDoctrine()->getManager()->flush();

                return new JsonResponse(['mensaje' => 'El administrador fue actualizado satisfactoriamente',
                    'nombre' => $usuario->getNombre(), 'correo' => $usuario->getEmail(), 'activo' => $usuario->getActivo()
                ]);
            } else {
                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                    'usuario' => $usuario,
                    'form_id' => 'usuario_edit',
                    'button_action'=>'Actualizar',
                    'title'=>'Editar usuario'
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('usuario/_new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'form_id' => 'usuario_edit',
            'button_action'=>'Actualizar',
            'title'=>'Editar usuario'
        ]);
    }

    /**
     * @Route("/{id}/delete", name="usuario_delete",options={"expose"=true})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest() || $usuario->getId()==$this->getUser()->getId())
            throw $this->createAccessDeniedException();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($usuario);
        $entityManager->flush();

        return new JsonResponse(['mensaje' => 'El administrador fue eliminado satisfactoriamente']);
    }
}
