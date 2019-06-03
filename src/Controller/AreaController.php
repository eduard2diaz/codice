<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Autor;
use App\Entity\BalanceAnual;
use App\Entity\Institucion;
use App\Form\AreaType;
use App\Services\AreaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/area")
 */
class AreaController extends AbstractController
{
    /**
     * @Route("/", name="area_index", methods={"GET"},options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        if ($this->isGranted('ROLE_SUPERADMIN'))
            $areas = $this->getDoctrine()->getRepository(Area::class)->findAll();
        else
            $areas = $this->getDoctrine()->getRepository(Area::class)->findByInstitucion($this->getUser()->getInstitucion());

        if ($request->isXmlHttpRequest())
            if ($request->get('_format') == 'xml') {
                $cadena = "";
                foreach ($areas as $value)
                    $cadena .= "<option value={$value->getId()}>{$value->getNombre()}</option>";
                return new Response($cadena);
            } else
                return $this->render('area/_table.html.twig', [
                    'areas' => $areas,
                ]);

        return $this->render('area/index.html.twig', [
            'areas' => $areas,
        ]);
    }

    /**
     * @Route("/new", name="area_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $area = new Area();
        if ($this->isGranted('ROLE_ADMIN')) {
            $area->setInstitucion($this->getUser()->getInstitucion());
            $area->setMinisterio($this->getUser()->getMinisterio());
            $area->setPais($this->getUser()->getPais());
        }

        $form = $this->createForm(AreaType::class, $area, ['action' => $this->generateUrl('area_new')]);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($area);
                $em->flush();
                return $this->json(['mensaje' => 'El área fue registrada satisfactoriamente',
                    'nombre' => $area->getNombre(),
                    'institucion' => $area->getInstitucion()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $area->getId())->getValue(),
                    'id' => $area->getId(),
                ]);
            } else {
                $page = $this->renderView('area/_form.html.twig', [
                    'form' => $form->createView(),
                ]);
                return $this->json(['form' => $page, 'error' => true,]);
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="area_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();


        $this->denyAccessUnlessGranted('EDIT', $area);
        $form = $this->createForm(AreaType::class, $area, ['action' => $this->generateUrl('area_edit', ['id' => $area->getId()])]);
        $form->handleRequest($request);

        $eliminable=$this->esEliminable($area);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($area);
                $em->flush();
                return $this->json(['mensaje' => 'El área fue actualizada satisfactoriamente',
                    'nombre' => $area->getNombre(),
                    'institucion' => $area->getInstitucion()->getNombre(),
                ]);
            } else {
                $page = $this->renderView('area/_form.html.twig', [
                    'area' => $area,
                    'eliminable'=>$eliminable,
                    'form' => $form->createView(),
                    'form_id' => 'area_edit',
                    'action' => 'Actualizar',
                ]);
                return $this->json(['form' => $page, 'error' => true]);
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'eliminable'=>$eliminable,
            'title' => 'Editar área',
            'action' => 'Actualizar',
            'form_id' => 'area_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="area_show",options={"expose"=true})
     */
    public function show(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $area);
        return $this->render('area/_show.html.twig', [
            'area' => $area,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="area_delete",options={"expose"=true})
     */
    public function delete(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $area->getId(), $request->query->get('_token')) || false==$this->esEliminable($area))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $area);
        $em = $this->getDoctrine()->getManager();
        $em->remove($area);
        $em->flush();
        return $this->json(['mensaje' => 'El área fue eliminada satisfactoriamente']);
    }

    //Funcionalidades ajax

    /**
     * @Route("/{id}/findByAutor", name="area_findbyautor", methods="GET",options={"expose"=true})
     * Se utiliza en el gestionar de autor por parte de los usuarios con roles : ROLE_ADMIN o ROLE_SUPERADMIN
     */
    public function findByAutor(Request $request, AreaService $areaService, Autor $autor): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $area = $autor->getArea();
        $areas = $areaService->areasHijas($area);
        $areas[] = $area;

        $cadena = "";
        foreach ($areas as $area)
            $cadena .= "<option value={$area->getId()}>{$area->getNombre()}</option>";

        return new Response($cadena);
    }


    /**
     * @Route("/{id}/findbyinstitucion", name="area_findbyinstitucion",options={"expose"=true})
     * Funcionalidad que retorna el listado de areas de una determinada institucion(Se usa en el gestionar de areas)
     */
    public function findbyInstitucion(Request $request, Institucion $institucion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $areas = $em->getRepository(Area::class)->findByInstitucion($institucion);

        $areas_array = [];
        foreach ($areas as $area)
            $areas_array[] = ['id' => $area->getId(), 'nombre' => $area->getNombre()];

        return $this->json($areas_array);
    }

    private function esEliminable(Area $area)
    {
        $em = $this->getDoctrine()->getManager();
        $entidades = [
            ['nombre' => Area::class, 'foranea' => 'padre'],
            ['nombre' => Autor::class, 'foranea' => 'area'],
            ['nombre' => BalanceAnual::class, 'foranea' => 'area'],
        ];

        foreach ($entidades as $value) {
            $result = $em->getRepository($value['nombre'])->findOneBy([$value['foranea'] => $area]);
            if(null!=$result)
                return false;
        }
        return true;
    }
}
