<?php

namespace App\Controller;

use App\Services\AreaService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Autor;


/**
 * @Route("/reporte")
 */
class ReporteController extends AbstractController
{
    /**
     * @Route("/autor/{id}/resumenperiodo", name="reporte_autorresumenperiodo",options={"expose"=true})
     * Metodo que devuelve el listado de publicaciones por periodo de un determinado autor
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
                    $resumen[] = ['entidad' => $tipo_hijo, 'total' => 1, 'propio' => $value->getAutor()->getId() == $autor->getId() ? 1 : 0];
                else {
                    $resumen[$posicion]['total']++;
                    if($value->getAutor()->getId() == $autor->getId())
                        $resumen[$posicion]['propio']++;
                }

                $posicionAutor=$this->buscarAutor($autores,$value->getAutor()->getNombre());
                if($posicionAutor!=-1){
                    $autores[$posicionAutor][$tipo_hijo]++;
                }
                    else{
                            $autores[]=['autor'=>$value->getAutor()->getNombre(),"Evento"=>0,"Premio"=>0,"Tesis"=>0,"Software"=>0,"Patente"=>0,"Norma"=>0,"Monografia"=>0,"Libro"=>0,"Articulo"=>0];
                            $autores[count($autores)-1][$tipo_hijo]=1;
                    }

                $total++;
            }
        } else {
            $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE p.estado=1 AND a.id=:id AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin')
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

        return $this->json([
            'pdf' => $this->renderView('reporte/resumen_publicacionpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/resumen_publicacion.html.twig', $parameters),
            'data' => json_encode($resumen)
        ]);
    }

    /**
     * @Route("/rankingautor", name="reporte_rankingautor",options={"expose"=true})
     */
    public function rankingAutor(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT a.nombre, COUNT(p.id) as cantidad FROM App:Publicacion p JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion  AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin AND p.estado=1 GROUP BY a.nombre ORDER BY count(p.id) DESC');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $consulta->setMaxResults(20);
        $result=$consulta->getResult();
        $parameters=['title'=>'Ranking de autores', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/area", name="reporte_area",options={"expose"=true})
     */
    public function reporteArea(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT a2.nombre, COUNT(p.id) as cantidad FROM App:Publicacion p JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion AND p.estado= 1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin GROUP BY a2.nombre');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $result=$consulta->getResult();
        $parameters=['title'=>'Resumen por área', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/tipotesis", name="reporte_tipotesis",options={"expose"=true})
     */
    public function reporteTipoTesis(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT tt.nombre, COUNT(t.id) as cantidad FROM App:Tesis t JOIN t.tipoTesis tt JOIN t.id p  JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion AND p.estado= 1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin GROUP BY tt.nombre');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $result=$consulta->getResult();
        $parameters=['title'=>'Resumen por tipo de tesis', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/tipopremio", name="reporte_tipopremio",options={"expose"=true})
     */
    public function reporteTipoPremio(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT tp.nombre, COUNT(pm.id) as cantidad FROM App:Premio pm JOIN pm.tipoPremio tp JOIN pm.id p  JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion  AND p.estado= 1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin GROUP BY tp.nombre');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $result=$consulta->getResult();
        $parameters=['title'=>'Resumen por tipo de premio', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/tiposoftware", name="reporte_tiposoftware",options={"expose"=true})
     */
    public function reporteTipoSoftware(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT ts.nombre, COUNT(s.id) as cantidad FROM App:Software s JOIN s.tipoSoftware ts JOIN s.id p  JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion  AND p.estado= 1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin GROUP BY ts.nombre');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $result=$consulta->getResult();
        $parameters=['title'=>'Resumen por tipo de software', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/tipoarticulo", name="reporte_tipoarticulo",options={"expose"=true})
     */
    public function reporteTipoArticulo(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));
        $consulta=$em->createQuery('SELECT ta.nombre, COUNT(ar.id) as cantidad FROM App:Articulo ar JOIN ar.tipoArticulo ta JOIN ar.id p  JOIN p.autor a JOIN a.area a2 JOIN a2.institucion i WHERE i.id= :institucion AND p.estado= 1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin GROUP BY ta.nombre');
        $consulta->setParameters(['institucion'=>$this->getUser()->getInstitucion()->getId(),'finicio' => $finicio, 'ffin' => $ffin]);
        $result=$consulta->getResult();
        $parameters=['title'=>'Resumen por tipo de artículo', 'data'=>$result];
        return $this->json([
            'pdf' => $this->renderView('reporte/datatableClaveValorpdf.html.twig', $parameters),
            'html' => $this->renderView('reporte/datatableClaveValor.html.twig', $parameters),
        ]);
    }

    /**
     * @Route("/exportar", name="reporte_exportar", options={"expose"=true})
     */
    public function exportar(Request $request, Pdf $pdf)
    {
        $html = $request->request->get('form') ?? 'a';
        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'Resumen.pdf'
        );
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

    private function buscarAutor($listado, $autor)
    {
        $i = 0;
        foreach ($listado as $banderin) {
            if ($banderin['autor'] === $autor)
                return $i;
            $i++;
        }
        return -1;
    }
}
