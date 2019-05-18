<?php
/**
 * Created by PhpStorm.
 * User: eduardo
 * Date: 15/05/19
 * Time: 21:22
 */

namespace App\EventListener;

use App\Entity\Autor;
use App\Entity\LoginAccess;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class FailureLogin extends DefaultAuthenticationFailureHandler
{
    private $container;

    /**
     * FailureLogin constructor.
     * @param $doctrine
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $username = '';
        if ($request->request->has('_username')) {
            $username = $request->request->get('_username');
            $em = $this->getContainer()->get('doctrine')->getManager();

            $q = $em->getRepository(Autor::class)->createQueryBuilder('u')
                    ->where('u.usuario = :username OR u.email = :correo')
                    ->setParameter('username', $username)
                    ->setParameter('correo', $username)
                    ->getQuery();

            try {
                $usuario = $q->getSingleResult();
            } catch (NoResultException $e) {
                $usuario=null;
            }

            if ($usuario != null) {
                $accesos = $em->getRepository(LoginAccess::class)->findBy(['autor' => $usuario], ['fecha' => 'DESC']);
                $contador = count($accesos);
                //Si el usuario ha sido atacado mas de una vez y ha transcurrio mas de una hora del ultimo ataque, elimino el registro de ataques
                if ($contador > 0) {
                    $fechaUltimo = $accesos[0]->getFecha();
                    $now = new \DateTime('now -1 hour');
                    if ($now > $fechaUltimo) {
                        foreach ($accesos as $acceso)
                            $em->remove($acceso);
                        $em->flush();
                    }
                }
                //Solo se almacenan los ultimos 5 ataques, por tanto si este es el 5to ataque tambien se almacena
                if (count($accesos) <= 4) {
                    $loginAccess = new LoginAccess();
                    $loginAccess->setAutor($usuario);
                    $loginAccess->setAgent($request->headers->get('user-agent'));
                    $em->persist($loginAccess);
                    $em->flush();
                }
                //Si en menos de una hora el usuario ha sido atacado 5 o mas veces se desactiva su cuenta como mecanismo de seguridad
                if (count($accesos) == 4 && $usuario->getActivo() == true) {
                    $usuario->setActivo(false);
                    $em->persist($usuario);
                    $em->flush();
                    $emailService = $this->container->get('app.email_service');
                    $from = 'no-reply@unah.edu.cu';
                    $to = $usuario->getEmail();
                    $subject = 'Ataque a su cuenta de usuario';
                    $body = 'Ha ocurrido un ataque contra su cuenta. Contacte con el administrador para cambiar su contraseña y activar su cuenta';
                    $emailService->sendEmail($from, $to, $subject, $body);
                }

            }
        }
        $message = $this->getContainer()->get('translator')->trans($exception->getMessageKey());
        $parameters = [
            'error' => ['message' => $message],
            'last_username' => $username
        ];
        $html = $this->container->get('twig')->render('default/index.html.twig', $parameters);
        return new Response($html);
    }
}