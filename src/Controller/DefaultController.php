<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
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
            if($this->isGranted('ROLE_SUPERADMIN'))
                return $this->redirectToRoute('usuario_index');
            else
                return $this->redirectToRoute('autor_show',['id'=>$this->getUser()->getId()]);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('default/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
