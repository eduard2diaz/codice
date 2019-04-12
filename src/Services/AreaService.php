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
     * Funcionalidad que llama a la funcion recursiva areasHijasAux para obtener el listado de areas hijas de una
     * determinada area
    */
    public function areasHijas(Area $area)
    {
        $array = [];
        return $this->areasHijasAux($area, $array);
    }

    /*
     * Funcion recursiva que devuelve las areas hijas de una determinada area
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
     * Funcion que devuelve las areas no hojas de una determinada area
     */
    public function areasNoHijas(Area $area)
    {
        $hijas = $this->areasHijas($area);
        $hijas[]=$area;
        $em = $this->getEm();
/*        if (empty($hijas)) {
            $consulta = $em->createQuery('SELECT a FROM App:Area a JOIN a.institucion i WHERE a.id!= :id AND i.id= :institucion');
            $consulta->setParameters(['id' => $area->getId(), 'institucion' => $area->getInstitucion()->getId()]);
        } else {*/
            $consulta = $em->createQuery('SELECT a FROM App:Area a JOIN a.institucion i WHERE a.id!= :id AND i.id= :institucion AND NOT a  IN (:hijas)');
            $consulta->setParameters(array('hijas' => $hijas, 'institucion' => $area->getInstitucion()->getId(), 'id' => $area->getId()));
        //}
        return $consulta->getResult();
    }

    /*
     * Funcion que llama a la funcion recursiva subordinadosAux para obtener los subordinados de un determinado autor
     */
    public function subordinados(Autor $autor)
    {
        $array = array();
        return $this->subordinadosAux($autor, $array);
    }

    /*
     * Funcion recursiva que devuelve los subordinados de un determinado autor
     */
    private function subordinadosAux(Autor $autor, &$subordinados)
    {
        $em = $this->getEm();
        $hijos = $em->getRepository('App:Autor')->findByJefe($autor);
        foreach ($hijos as $hijo) {
            $subordinados[] = $hijo;
            $this->subordinadosAux($hijo, $subordinados);
        }
        return $subordinados;
    }

    /*
     * Funcion recursiva que devuelve los directivos de una determinada institucion, se puede utilizar la variable
     * ignoreIds para indicar el arreglo de ids del autor que se debe ignorar.
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
     * Funcion de devuelve los directivos subordinados de un determinado autor
     */
    public function obtenerDirectivosSubordinados(Autor $autor)
    {
        $directivos = $this->obtenerDirectivos($autor->getInstitucion()->getId(), [$autor->getId()]);
        $result = [];
        foreach ($directivos as $value) {
            if ($value->esSubordinado($autor)) {
                $result[] = $value;
            }
        }
        return $result;
    }

    /*
     * Funcion que devuelve los directivos no subordinados de un determinado usuario
     */
    public function obtenerDirectivosNoSubordinados(Autor $autor)
    {
        $directivos = $this->obtenerDirectivosSubordinados($autor);
        $directivos[] = $autor;
        return $this->obtenerDirectivos($autor->getInstitucion()->getId(), $directivos);
    }


}