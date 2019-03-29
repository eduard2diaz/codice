<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('autor_show',['id'=>$this->getUser()->getId()]);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('default/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/prueba", name="prueba")
     */
    public function prueba(Request $request){
        $query=$request->get('query');
        $content='';
        $em=$this->getDoctrine()->getManager();
        $consulta=$em->createQuery('SELECT u.id, u.nombre,u.rutaFoto FROM App:Autor u WHERE u.nombre like :parametro');
        $consulta->setParameter('parametro','%'.$query.'%');
        $consulta->setMaxResults(5);
        $usuarios=$consulta->getResult();

        $consulta=$em->createQuery('SELECT p.id, p.titulo FROM App:Publicacion p WHERE p.titulo like :parametro');
        $consulta->setParameter('parametro','%'.$query.'%');
        $consulta->setMaxResults(5);
        $publicaciones=$consulta->getResult();
        $content=$this->renderView('default/searchresult.html.twig',['usuarios'=>$usuarios,'publicaciones'=>$publicaciones]);

        return new Response($content);
    }
}
