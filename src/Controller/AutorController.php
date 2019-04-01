<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Services\AreaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/autor")
 */
class AutorController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="autor_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor, AreaService $areaService): Response
    {
        if ($this->isGranted('ROLE_ADMIN'))
            $autors = $this->getDoctrine()->getManager()->createQuery('SELECT u FROM App:Autor u WHERE u.id!=:id')->setParameter('id', $this->getUser()->getId())->getResult();
        else
            $autors = $areaService->subordinados($autor);

        if ($request->isXmlHttpRequest())
            return $this->render('autor/_table.html.twig', ['autors' => $autor]);

        return $this->render('autor/index.html.twig', [
            'autors' => $autors,

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/new", name="autor_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $autor = new Autor();
        if (in_array('ROLE_DIRECTIVO', $this->getUser()->getRoles()))
            $autor->setJefe($this->getUser());

        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($autor);
            $entityManager->flush();
            $this->addFlash('success', 'El usuario fue registrado satisfactoriamente');
            return $this->redirectToRoute('autor_index', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('autor/new.html.twig', [
            'autor' => $autor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="autor_show", methods={"GET"})
     */
    public function show(Autor $autor): Response
    {
        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
            'allow_edit' => ($this->isGranted('ROLE_ADMIN') || $autor->getId() == $this->getUser()->getId() ||
                $autor->esJefe($this->getUser())
            ),
            'follow_button' => $autor->getSeguidores()->contains($this->getUser()) == false,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="autor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Autor $autor, UserPasswordEncoderInterface $encoder): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $autor);
        $form = $this->createForm(AutorType::class, $autor);
        $passwordOriginal = $form->getData()->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ruta = $this->getParameter('storage_directory');
            if (null == $autor->getPassword())
                $autor->setPassword($passwordOriginal);
            else
                $autor->setPassword($encoder->encodePassword($autor, $autor->getPassword()));

            if ($autor->getFile() != null) {
                if ($autor->getRutaFoto() != null)
                    $autor->actualizarFoto($ruta);
                else
                    $autor->Upload($ruta);
                $autor->setFile(null);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'El usuario fue actualizado satisfactoriamente');
            return $this->redirectToRoute('autor_show', ['id' => $autor->getId()]);
        }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form->createView(),

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="autor_delete", options={"expose"=true})
     */
    public function delete(Request $request, Autor $autor): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $autor);
        $em = $this->getDoctrine()->getManager();
        $em->remove($autor);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El usuario fue eliminado satisfactoriamente'));
    }

    //ajax

    /**
     * @Route("/ajax", name="autor_ajax", options={"expose"=true})
     * Esta funcionalidad se utiliza para enviar un mensaje pues es la que permite filtrar los usuarios
     */
    public function ajax(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $em = $this->getDoctrine()->getManager();
            $parameter = $request->get('q');
            $query = $em->createQuery('SELECT u.id, u.nombre as text FROM App:Autor u WHERE u.nombre LIKE :nombre ORDER BY u.nombre ASC')
                ->setParameter('nombre', '%' . $parameter . '%');
            $result = $query->getResult();
            return new Response(json_encode($result));
        }
        return new Response(json_encode($result));
    }

    /**
     * @Route("/{id}/subscripcion", name="autor_subscripcion", options={"expose"=true})
     * Esta funcionalidad se utiliza que un usuario se subscriba como seguir de otro
     */
    public function subscripcion(Request $request, Autor $autor): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('SEGUIR', $autor);
        if (!$autor->getSeguidores()->contains($this->getUser())) {
            $autor->addSeguidores($this->getUser());
            $parameters = ['class' => 'flaticon flaticon-close', 'label' => 'Dejar de seguir', 'mensaje' => 'Tu subscripción ha sido registrada'];
        } else {
            $parameters['class'] = 'flaticon flaticon-user-add';
            $parameters['label'] = 'Seguir';
            $parameters['mensaje'] = 'Tu subscripción fue eliminada';
            $autor->removeSeguidores($this->getUser());
        }


        $em = $this->getDoctrine()->getManager();
        $em->persist($autor);
        $em->flush();
        return new JsonResponse($parameters);
    }

    /**
     * @Route("/{id}/seguidores", name="autor_seguidores", options={"expose"=true})
     * Retorna el listado de seguidores de un determinado usuario
     */
    public function seguidores(Autor $autor): Response
    {
        $seguidores = $autor->getSeguidores()->toArray();
        return $this->render('autor/seguidores.html.twig', [
            'autors' => $seguidores,

            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/sugerir", name="autor_sugerir", options={"expose"=true})
     * Retorna el listado de sugerencias de autores
     */
    public function sugerirAutores()
    {
        $em = $this->getDoctrine()->getManager();
        $seguidos = $this->getUser()->getSeguidor()->toArray();
        if(count($seguidos)>0){
        $consulta = $em->createQuery('SELECT a.id, a.nombre, a.rutaFoto,i.nombre as institucion FROM App:Autor a join a.institucion i WHERE a.id != :id AND a.id NOT IN (:seguidos) AND a.activo=true');
        $consulta->setParameters(['id' => $this->getUser()->getId(), 'seguidos' => $seguidos]);
        }else{
            $consulta = $em->createQuery('SELECT a.id, a.nombre, a.rutaFoto,i.nombre as institucion FROM App:Autor a join a.institucion i WHERE a.id != :id AND a.activo=true');
            $consulta->setParameters(['id' => $this->getUser()->getId()]);
        }
        //$consulta->setMaxResults(4);
        $datos = $consulta->getResult();

        $cantidad = count($datos);
        $grupos = $cantidad >= 4 ? 4 : $cantidad;
        $aux = array();
        if ($grupos > 1) {
            $keys = array_rand($datos, $grupos);
            foreach ($keys as $value) {
                $aux[] = $datos[$value];
            }
        } else
            if ($grupos == 1) {
                $aux = $datos;
            }

        return $this->render('autor/sugerencia.html.twig', ['datos' => $aux]);
    }
}
