<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/*
 *Listener que se ejecuta cuando un usuario se desautentica en el sistema
 */
class LogoutListener  implements LogoutHandlerInterface
{
    private $doctrine;

    /**
     * LogoutListener constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return mixed
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    public function logout(Request $Request, Response $Response, TokenInterface $token) {
        $em=$this->getDoctrine()->getManager();
        //Si el usuario no es anonimo, actualizo su fecha de ultimo logout
        if(count($token->getRoles())>0){
            $token->getUser()->setUltimologout(new \DateTime());
            $em->persist($token->getUser());
            $em->flush();
        }
    }

}
