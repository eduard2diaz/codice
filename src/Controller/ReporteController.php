<?php

namespace App\Controller;

use App\Services\AreaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reporte")
 */
class ReporteController extends AbstractController
{
    /**
     * @Route("/resumen", name="reporte_resumen")
     */
    public function index(AreaService $areaService)
    {
        $subordinados=$areaService->subordinados($this->getUser());
        $em=$this->getDoctrine()->getManager();
        $publicaciones=$em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE a.id=:id OR a.id IN (:subordinados)')
                          ->setParameters(['subordinados'=>$subordinados,'id'=>$this->getUser()->getId()])
                          ->getResult();

        $resumen=[];
        $total=0;
        foreach ($publicaciones as $value){
            $tipo_hijo = substr($value->getChildType(), 11);
            $posicion=$this->buscarTipoPublicacion($resumen,$tipo_hijo);
            if(-1==$posicion)
                $resumen[]=['entidad'=>$tipo_hijo,'total'=>1,'propio'=>$value->getAutor()->getId()==$this->getUser()->getId() ? 1 : 0];
            else{
                $resumen[$posicion]['total']++;
                $resumen[$posicion]['propio']++;
            }
            $total++;
        }

        return $this->render('reporte/resumen_publicacion.html.twig', [
            'resumen' => $resumen,
            'resumen_json' => json_encode($resumen),
            'total' => $total,
        ]);
    }

    private function buscarTipoPublicacion($arreglo,$tipohijo){
        $i=0;
        foreach ($arreglo as $value){
            if($value['entidad']==$tipohijo)
                return $i;
            $i++;
        }
        return -1;
    }
}
