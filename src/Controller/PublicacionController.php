<?php

namespace App\Controller;

use App\Entity\Publicacion;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/publicacion")
 */
class PublicacionController extends AbstractController
{
    /**
     * @Route("/{id}/show", name="publicacion_show", methods={"GET"})
     */
    public function show(Publicacion $publicacion): Response
    {
        $entidad = $publicacion->getChildType();
        $ruta = strtolower(substr($entidad, 11));
        $ruta = $ruta . '_show';
        return $this->redirectToRoute($ruta, ['id' => $publicacion->getId()]);
    }

    /**
     * @Route("/{id}/exportar", name="publicacion_exportar", methods={"GET"})
     */
    public function exportar(Publicacion $publicacion,Pdf $pdf): Response
    {
        $entidad_name = $publicacion->getChildType();
        $ruta = strtolower(substr($entidad_name, 11));
        $entidad=$this->getDoctrine()->getManager()->getRepository($entidad_name)->find($publicacion);
        $html=$this->renderView($ruta.'/_exportar.html.twig',['entidad'=>$entidad]);
        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            $publicacion->getTitulo().'.pdf'
        );
    }

    /**
     * @Route("/{id}/descargar", name="publicacion_descargar", methods={"GET"})
     */
    public function descargar(Publicacion $publicacion): Response
    {
        $ruta = $this->getParameter('storage_directory') . DIRECTORY_SEPARATOR . $publicacion->getRutaArchivo();

        if (!file_exists($ruta))
            throw $this->createNotFoundException();

        $archivo = file_get_contents($ruta);
        return new Response($archivo, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Transfer-Encoding' => 'binary',
            'Content-length' => strlen($archivo),
            'Pragma' => 'no-cache',
            'Expires' => '0'));
    }

    /**
     * @Route("/search", name="publicacion_search",options={"expose"=true})
     */
    public function search(Request $request,PaginatorInterface $paginator)
    {
        $query = $request->get('query');
        if(!$query || $query=='')
            throw new \LogicException('Falta el parámetro query');

        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()) {
            $content = '';
            $consulta = $em->createQuery('SELECT u.id, u.nombre,u.rutaFoto FROM App:Autor u WHERE u.nombre like :parametro');
            $consulta->setParameter('parametro', '%' . $query . '%');
            $consulta->setMaxResults(5);
            //Obtengo el listado de usuarios
            $usuarios = $consulta->getResult();
            $consulta = $em->createQuery('SELECT p.id, p.titulo FROM App:Publicacion p WHERE p.titulo like :parametro');
            $consulta->setParameter('parametro', '%' . $query . '%');
            $consulta->setMaxResults(5);
            //Obtengo el listado de publicaciones
            $publicaciones = $consulta->getResult();
            $content = $this->renderView('publicacion/search_quickresult.html.twig', ['query'=>$query,'usuarios' => $usuarios, 'publicaciones' => $publicaciones]);
            return new Response($content);
        }

        $consulta = $em->createQuery('SELECT u.id, u.nombre,u.rutaFoto,u.ultimoLogin, u.ultimoLogout, i.nombre as institucion,p.nombre as pais, 1 as esAutor FROM App:Autor u JOIN u.institucion i join i.pais p WHERE u.nombre like :parametro');
        $consulta->setParameter('parametro', '%' . $query . '%');
        $usuarios = $consulta->getResult();

        $consulta = $em->createQuery('SELECT p.id, p.titulo, a.nombre as autor, 0 as esAutor FROM App:Publicacion p JOIN p.autor a WHERE p.titulo like :parametro');
        $consulta->setParameter('parametro', '%' . $query . '%');
        $publicaciones = $consulta->getResult();

        $pagination = $paginator->paginate(
            //$consulta,
            array_merge($usuarios,$publicaciones), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            1 /*limit per page*/
        );
        return $this->render('publicacion/search_result.html.twig', array('pagination' => $pagination));


    }

}
