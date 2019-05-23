<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Entity\Institucion;
use App\Form\AutorType;
use App\Services\AreaService;
use App\Tool\FileStorageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/autor")
 */
class AutorController extends AbstractController
{
    /**
     * @Route("/indexall", name="autor_indexall", methods={"GET"})
     * Listado de todos los autores de la aplicacion, es usado exclusivamente por el ROLE_SUPERADMIN
     */
    public function indexAll(Request $request): Response
    {
        $autors = $this->getDoctrine()->getManager()->createQuery('SELECT u FROM App:Autor u')->getResult();

        if ($request->isXmlHttpRequest())
            return $this->render('autor/_table.html.twig', ['autors' => $autors,'esGestor'=>true]);

        return $this->render('autor/indexall.html.twig', [
            'autors' => $autors,
            'esGestor'=>true
        ]);
    }

    /**
     * @Route("/{id}/index", name="autor_index", methods={"GET"})
     */
    public function index(Request $request, Autor $autor, AreaService $areaService): Response
    {
        if(!$autor->esDirectivo())
            throw new \LogicException('El autor indicado no es un directivo');

        if (in_array('ROLE_ADMIN',$autor->getRoles()))
            $autors = $this->getDoctrine()->getManager()->createQuery('SELECT u FROM App:Autor u JOIN u.institucion i WHERE u.id!=:id AND i.id= :institucion')->setParameters(['id' => $this->getUser()->getId(), 'institucion' => $this->getUser()->getInstitucion()->getId()])->getResult();
        else
            $autors = $areaService->subordinados($autor);

        $esGestor=$this->isGranted('ROLE_SUPERADMIN') ||
            ($this->isGranted('ROLE_ADMIN') && $this->getUser()->getInstitucion()->getId()==$autor->getInstitucion()->getId())
            || $this->getUser()->getId()==$autor->getId()
            || $autor->esSubordinado($this->getUser());

        if ($request->isXmlHttpRequest())
            return $this->render('autor/_table.html.twig', ['autors' => $autors,'esGestor' => $esGestor]);

        return $this->render('autor/index.html.twig', [
            'esGestor' => $esGestor,
            'autors' => $autors,
            'esDirectivo' => $autor->esDirectivo(),
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
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $autor->setInstitucion($this->getUser()->getInstitucion());
            $autor->setMinisterio($this->getUser()->getMinisterio());
            $autor->setPais($this->getUser()->getPais());
        }

        if ($this->isGranted('ROLE_DIRECTIVO'))
            $autor->setJefe($this->getUser());

        $form = $this->createForm(AutorType::class, $autor, ['action' => $this->generateUrl('autor_new')]);
        $form->handleRequest($request);

        if ($form->isSubmitted())

            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();

            elseif ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($autor);
                $entityManager->flush();
                $this->addFlash('success', 'El usuario fue registrado satisfactoriamente');
                if ($this->isGranted('ROLE_SUPERADMIN'))
                    $route = $this->generateUrl('autor_indexall');
                else
                    $route = $this->generateUrl('autor_index', ['id' => $this->getUser()->getId()]);
                return $this->json(['ruta' => $route]);
            } else {
                $page = $this->renderView('autor/_form.html.twig', [
                    'form' => $form->createView(),
                    'autor' => $autor,
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
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
            'allow_edit' => $this->isGranted('ROLE_SUPERADMIN') || $autor->getId() == $this->getUser()->getId() || (($this->getUser()->getInstitucion()->getId()==$autor->getInstitucion()->getId()) && ($this->isGranted('ROLE_ADMIN') || $autor->esSubordinado($this->getUser()))),
            'follow_button' => $autor->getSeguidores()->contains($this->getUser()) == false,
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
            'esDirectivo' => $autor->esDirectivo(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="autor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Autor $autor, UserPasswordEncoderInterface $encoder): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $autor);

        /*
         *Sucedia que cunado modificaba las credenciales(usuario,correo) del autor actualmente autenticado, si
         * ocurria un error de validacion con las mismas, el autor quedaba deslogueado, pues Sf no podia refrecar
         * el token de autenticacion, por eso guardo un clon del usuario actual
         */
        if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getId() == $autor->getId())
            $clon = clone $autor;

        $form = $this->createForm(AutorType::class, $autor, ['action' => $this->generateUrl('autor_edit', ['id' => $autor->getId()])]);
        $passwordOriginal = $form->getData()->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();

            elseif ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');

                if (null == $autor->getPassword())
                    $autor->setPassword($passwordOriginal);
                else
                    $autor->setPassword($encoder->encodePassword($autor, $autor->getPassword()));

                if ($autor->getFile() != null) {
                    if ($autor->getRutaFoto() != null){
                        $rutaArchivo = $ruta . DIRECTORY_SEPARATOR . $autor->getRutaFoto();
                        FileStorageManager::removeUpload($rutaArchivo);
                    }
                        $autor->setRutaFoto(FileStorageManager::Upload($ruta,$autor->getFile()));
                    $autor->setFile(null);
                }

                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'El usuario fue actualizado satisfactoriamente');
                return $this->json(['ruta' => $this->generateUrl('autor_show', ['id' => $autor->getId()])]);
            } else {

                /*
                 * Y si ocurre un error simplemente refresco el token de autenticacion usando las credenciales antiguas
                 */
                if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getId() == $autor->getId()) {
                    $autor = $clon;
                    $this->container->get('security.token_storage')->setToken(new UsernamePasswordToken($autor, $autor->getPassword(), 'chain_provider', $autor->getRoles()));
                }

                $page = $this->renderView('autor/_form.html.twig', [
                    'form' => $form->createView(),
                    'autor' => $autor,
                    'form_title'=>'Actualizar perfil',
                    'button_action'=>'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form->createView(),
            'esDirectivo' => $autor->esDirectivo(),
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
        if (!$request->isXmlHttpRequest()  || !$this->isCsrfTokenValid('delete'.$autor->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $autor);
        $em = $this->getDoctrine()->getManager();
        $em->remove($autor);
        $em->flush();
        return $this->json(['mensaje' => 'El usuario fue eliminado satisfactoriamente']);
    }

    //Funcionalidades ajax
    /**
     * @Route("/searchfilter", name="autor_searchfilter", options={"expose"=true})
     * Esta funcionalidad se utiliza para enviar un mensaje pues es la que permite filtrar los usuarios
     */
    public function searchFilter(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $em = $this->getDoctrine()->getManager();
            $parameter = $request->get('q');
            $query = $em->createQuery('SELECT u.id, u.nombre as text, u.rutaFoto as foto FROM App:Autor u WHERE upper(u.nombre) LIKE :nombre ORDER BY u.nombre ASC')
                ->setParameter('nombre', '%' . strtoupper($parameter)  . '%');
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
        return $this->json($parameters);
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
            'esDirectivo' => $autor->esDirectivo(),
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/{id}/seguidos", name="autor_seguidos", options={"expose"=true})
     * Retorna el listado de seguidores de un determinado usuario
     */
    public function seguidos(Autor $autor): Response
    {
        $seguidos = $autor->getSeguidor()->toArray();
        return $this->render('autor/seguidos.html.twig', [
            'autors' => $seguidos,
            'esDirectivo' => $autor->esDirectivo(),
            'user_id' => $autor->getId(),
            'user_foto' => null != $autor->getRutaFoto() ? $autor->getRutaFoto() : null,
            'user_nombre' => $autor->__toString(),
            'user_correo' => $autor->getEmail(),
        ]);
    }

    /**
     * @Route("/sugerir", name="autor_sugerir", options={"expose"=true})
     * Retorna el listado de sugerencias de autores(SE UTILIZA EN LAS BUSQUEDAS)
     */
    public function sugerirAutores()
    {
        $id=$this->getUser()->getId();
        $seguidos="$id";
        foreach ($this->getUser()->getSeguidor()->toArray() as $value){
            $aux=$value->getId();
            $seguidos.=",$aux";
        }

        $conn=$this->getDoctrine()->getConnection();
        $sql="SELECT a.id, a.nombre, a.ruta_foto as rutafoto, i.nombre as institucion FROM autor as a JOIN institucion i ON a.institucion = i.id WHERE a.id::text  NOT IN (:lista) ORDER BY random() LIMIT 4";
        $statement=$conn->prepare($sql);
        $statement->execute(['lista'=>$seguidos]);
        return $this->render('autor/_sugerencia.html.twig', ['datos' => $statement->fetchAll()]);
    }

    /**
     * @Route("/{id}/finddirectivosbyinstitucion", name="autor_finddirectivosbyinstitucion", options={"expose"=true})
     * Retorna el listado de directivos que posee una determinada institucion se  usan en el gestionar de autores
     */
    public function findDirectivosByInstitucion(Request $request, AreaService $areaService, Institucion $institucion)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $directivos = $areaService->obtenerDirectivos($institucion->getId());
        $directivos_array = [];
        foreach ($directivos as $obj)
            $directivos_array[] = ['id' => $obj->getId(), 'nombre' => $obj->getNombre()];

        return $this->json($directivos_array);
    }
}
