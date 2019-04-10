<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/5/2018
 * Time: 11:41
 */

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Entity\Area;
use App\Entity\Autor;

class AreaService
{
    private $em;

    /**
     * AreaService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return mixed
     */
    public function getEm()
    {
        return $this->em;
    }

    /*
     * FUNCION QUE LLAMA A UNA FUNCION RECURSIVA PARA OBTENER LAS AREAS HIJAS DE UNA DETERMINADA AREA
    */
    public function areasHijas(Area $area)
    {
        //if (!$esAdmin) {
            $array = [$area];
            return $this->areasHijasAux($area, $array);
       // } else
         //   return $this->getEm()->getRepository('App:Area')->findAll();
    }

    /*
     * FUNCION RECURSIVA QUE DEVUELVE LAS AREAS HIJAS DE UNA AREA
     */
    private function areasHijasAux(Area $area, &$areas)
    {

        $em = $this->getEm();
        $hijos = $em->createQuery('SELECT a FROM App:Area a JOIN a.padre p WHERE p.id=:id')->setParameter('id', $area->getId())->getResult();
        foreach ($hijos as $hijo) {
            $areas[] = $hijo;
            $this->areasHijasAux($hijo, $areas);
        }
        return $areas;
    }

    /*
     * FUNCION QUE A PARTIR DE LOS DATOS OBTENIDOS DE LA FUNCION ANTERIOR, DEVUELVE LAS AREAS NO HIJAS DE
     * UNA DETERMINADA AREA
     */
    public function areasNoHijas(Area $area)
    {
        $hijas = $this->areasHijas($area);
        $em = $this->getEm();
        if (empty($hijas)) {
            $consulta = $em->createQuery('SELECT a FROM App:Area a JOIN a.institucion i WHERE a.id!= :id AND i.id= :institucion');
            $consulta->setParameters(['id' => $area->getId(), 'institucion' => $area->getInstitucion()->getId()]);
        } else {
            $consulta = $em->createQuery('SELECT a FROM App:Area a JOIN a.institucion i WHERE a.id!= :id AND i.id= :institucion AND NOT a  IN (:hijas)');
            $consulta->setParameters(array('hijas' => $hijas, 'institucion' => $area->getInstitucion()->getId(), 'id' => $area->getId()));
        }
        return $consulta->getResult();
    }

    /*
     * FUNCION QUE LLAMA A LA FUNCION RECURSIVA PARA OBTENER LOS SUBORDINADOS
     */
    public function subordinados(Autor $usuario)
    {
        $array = array();
        return $this->subordinadosAux($usuario, $array);
    }

    /*
     * FUNCION RECURSIVA QUE OBTIENE LOS SUPORDINADOS DE UNA DETERMINADA PERSONA
     */
    private function subordinadosAux(Autor $usuario, &$subordinados)
    {
        $em = $this->getEm();
        $hijos = $em->getRepository('App:Autor')->findByJefe($usuario);
        foreach ($hijos as $hijo) {
            $subordinados[] = $hijo;
            $this->subordinadosAux($hijo, $subordinados);
        }
        return $subordinados;
    }

    /*
     * OBTIENE EL LISTADO DE DIRECTIVOS DE LA INSTITUCION
     */
    public function obtenerDirectivos($institucion, $ignoreIds = null)
    {
        if (!$ignoreIds)
            $consulta = $this->getEm()->createQuery("SELECT u FROM App:Autor u join u.institucion i join u.idrol r WHERE i.id= :institucion AND r.nombre= :nombre")->setParameters(array('nombre' => 'ROLE_DIRECTIVO', 'institucion' => $institucion));
        else
            $consulta = $this->getEm()->createQuery("SELECT u FROM App:Autor u join u.institucion i join u.idrol r WHERE u.id NOT IN (:id) AND i.id= :institucion AND r.nombre= :nombre")->setParameters(array('id' => $ignoreIds, 'institucion' => $institucion, 'nombre' => 'ROLE_DIRECTIVO'));
        return $consulta->getResult();
    }

    /*
     * OBTIENE EL LISTADO DE DIRECTIVOS DE LA INSTITUCION que se subordinan a un determinado usuario
     */
    public function obtenerDirectivosSubordinados(Autor $autor)
    {
        $directivos = $this->obtenerDirectivos($autor->getInstitucion()->getId(),[$autor->getId()]);
        $result=[];
        foreach ($directivos as $value) {
            if($value->esSubordinado($autor)) {
                $result[]=$value;
            }
        }
        return $result;
    }

    /*
     * OBTIENE EL LISTADO DE DIRECTIVOS DE LA INSTITUCION que no se subordinan a un determinado usuario
     */
    public function obtenerDirectivosNoSubordinados(Autor $autor)
    {
        $directivos = $this->obtenerDirectivosSubordinados($autor);
        $directivos[]=$autor;
        return $this->obtenerDirectivos($autor->getInstitucion()->getId(),$directivos);
    }


}