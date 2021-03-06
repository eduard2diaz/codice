<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/5/2018
 * Time: 11:41
 */

namespace App\Services;
use App\Entity\Notificacion;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

class NotificacionService
{
    private $em;

    /**
     * NotificacionService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    public function getEm(): EntityManager
    {
        return $this->em;
    }

    public function nuevaNotificacion($destinatario,$descripcion){
        if(null==$destinatario || null==$descripcion)
            throw new \Exception('No fueron enviados suficientes parámetros');

        $destinatario=$this->getEm()->getRepository('App:Autor')->find($destinatario);
        if(null==$destinatario)
            throw new \Exception('El destinatario no existe');

        $notificacion=new Notificacion();
        $notificacion->setFecha(new \DateTime());
        $notificacion->setDestinatario($destinatario);
        $notificacion->setDescripcion($descripcion);
        $this->getEm()->merge($notificacion);
        $this->getEm()->flush();
    }

    public function nuevaNotificacionPersist($destinatario,$descripcion){
        if(null==$destinatario || null==$descripcion)
            throw new \Exception('No fueron enviados suficientes parámetros');

        $destinatario=$this->getEm()->getRepository('App:Autor')->find($destinatario);
        if(null==$destinatario)
            throw new \Exception('El destinatario no existe');

        $notificacion=new Notificacion();
        $notificacion->setFecha(new \DateTime());
        $notificacion->setDestinatario($destinatario);
        $notificacion->setDescripcion($descripcion);
        $this->getEm()->persist($notificacion);
        $this->getEm()->flush();
    }

}