<?php

namespace App\Controller;

use App\Services\AreaService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Autor;


/**
 * @Route("/reporte")
 */
class ReporteController extends AbstractController
{
    /**
     * @Route("/autor/{id}/resumenperiodo", name="reporte_autorresumenperiodo",options={"expose"=true})
     */
    public function resumenAutor(Request $request, AreaService $areaService, Autor $autor)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEWSTATICS', $autor);

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));

        $resumen = [];
        $total = 0;
        $esDirectivo = in_array('ROLE_DIRECTIVO', $autor->getRoles()) || in_array('ROLE_ADMIN', $autor->getRoles());
        if ($esDirectivo == true) {
            $subordinados = $areaService->subordinados($autor);
            if (count($subordinados) == 0)
                $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE a.id=:id AND p.estado=1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin')
                    ->setParameters(['id' => $autor->getId(), 'finicio' => $finicio, 'ffin' => $ffin])
                    ->getResult();
            else
                $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE (a.id=:id OR a.id IN (:subordinados) ) AND p.fechaCaptacion>= :finicio AND p.estado=1 AND p.fechaCaptacion<= :ffin')
                    ->setParameters(['id' => $autor->getId(), 'finicio' => $finicio, 'ffin' => $ffin, 'subordinados' => $subordinados])
                    ->getResult();

            $autores = [];
            foreach ($publicaciones as $value) {
                $tipo_hijo = substr($value->getChildType(), 11);
                $posicion = $this->buscarTipoPublicacion($resumen, $tipo_hijo);
                if (-1 == $posicion)
                    $resumen[] = ['entidad' => $tipo_hijo, 'total' => 1, 'propio' => $value->getAutor()->getId() == $this->getUser()->getId() ? 1 : 0];
                else {
                    $resumen[$posicion]['total']++;
                    $resumen[$posicion]['propio']++;
                }
                $posicion=$this->buscarAutor($autores,$value->getAutor()->getNombre());
                if($posicion!=-1){
                    $autores[$posicion][$tipo_hijo]++;
                }
                    else{
                            $autores[]=['autor'=>$value->getAutor()->getNombre(),"Encuentro"=>0,"Premio"=>0,"Tesis"=>0,"Software"=>0,"Patente"=>0,"Norma"=>0,"Monografia"=>0,"Libro"=>0,"Articulo"=>0];
                            $autores[count($autores)-1][$tipo_hijo]=1;
                    }

                $total++;
            }
        } else {
            $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE a.id=:id AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin')
                ->setParameters(['id' => $autor->getId(), 'finicio' => $finicio, 'ffin' => $ffin])
                ->getResult();


            foreach ($publicaciones as $value) {
                $tipo_hijo = substr($value->getChildType(), 11);
                $posicion = $this->buscarTipoPublicacion($resumen, $tipo_hijo);
                if (-1 == $posicion)
                    $resumen[] = ['entidad' => $tipo_hijo, 'total' => 1];
                else {
                    $resumen[$posicion]['total']++;
                }
                $total++;
            }
        }

        $parameters=[
            'finicio' => $finicio,
            'ffin' => $ffin,
            'resumen' => $resumen,
            'total' => $total,
            'esDirectivo' => $esDirectivo,
            'autor' => $autor->getNombre()
        ];
        if($esDirectivo)
            $parameters['resumen_subordinados']=$autores;

        return new JsonResponse([
            'pdf' => $this->renderView('reporte/resumen_publicacionpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/resumen_publicacion.html.twig', $parameters),
            'data' => json_encode($resumen)
        ]);
    }

    private function buscarTipoPublicacion($arreglo, $tipohijo)
    {
        $i = 0;
        foreach ($arreglo as $value) {
            if ($value['entidad'] == $tipohijo)
                return $i;
            $i++;
        }
        return -1;
    }

    public function buscarAutor($listado, $autor)
    {
        $i = 0;
        foreach ($listado as $banderin) {
            if ($banderin['autor'] == $autor) ;
            return $i;
            $i++;
        }
        return -1;
    }

    /**
     * @Route("/exportar", name="reporte_exportar", options={"expose"=true})
     */
    public function exportar(Request $request, Pdf $pdf)
    {
        $html = $request->request->get('form');

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'Resumen.pdf'
        );
    }
}
