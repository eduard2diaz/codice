<?php

namespace App\Controller;

use App\Services\AreaService;
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
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEWSTATICS', $autor);

        $em = $this->getDoctrine()->getManager();
        $finicio = new \DateTime($request->request->get('finicio'));
        $ffin = new \DateTime($request->request->get('ffin'));

        $resumen = [];
        $total = 0;
        $esDirectivo=in_array('ROLE_DIRECTIVO', $autor->getRoles());
        if ($esDirectivo==true) {
            $subordinados = $areaService->subordinados($autor);
            if(count($subordinados)==0)
                $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE a.id=:id AND p.estado=1 AND p.fechaCaptacion>= :finicio AND p.fechaCaptacion<= :ffin')
                    ->setParameters(['id' => $autor->getId(), 'finicio' => $finicio, 'ffin' => $ffin])
                    ->getResult();
            else
                $publicaciones = $em->createQuery('SELECT p FROM App:Publicacion p JOIN p.autor a WHERE (a.id=:id OR a.id IN (:subordinados) ) AND p.fechaCaptacion>= :finicio AND p.estado=1 AND p.fechaCaptacion<= :ffin')
                    ->setParameters(['id' => $autor->getId(), 'finicio' => $finicio, 'ffin' => $ffin,'subordinados'=>$subordinados])
                    ->getResult();

            foreach ($publicaciones as $value) {
                $tipo_hijo = substr($value->getChildType(), 11);
                $posicion = $this->buscarTipoPublicacion($resumen, $tipo_hijo);
                if (-1 == $posicion)
                    $resumen[] = ['entidad' => $tipo_hijo, 'total' => 1, 'propio' => $value->getAutor()->getId() == $this->getUser()->getId() ? 1 : 0];
                else {
                    $resumen[$posicion]['total']++;
                    $resumen[$posicion]['propio']++;
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

        return new JsonResponse([
            'html' => $this->renderView('reporte/resumen_publicacion.html.twig', [
                'resumen' => $resumen,
                'total' => $total,
                'esDirectivo'=>$esDirectivo,
                'autor'=>$autor->getNombre()
            ]),
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
}
