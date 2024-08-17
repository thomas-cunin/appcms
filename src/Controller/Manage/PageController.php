<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\ContentPage;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\Page;
use App\Service\PageTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    public function __construct(private PageTypeService $pageTypeService)
    {
    }

    /**
     * Retrieves the HTML content for the page types panel.
     *
     * @param Application $application The application instance.
     * @return JsonResponse The JSON response containing the HTML content.
     */
    #[Route('/app/menu/{parentMenu}/page/add/getPanel', name: 'app_menu_get_page_types_panel')]
    public function getPageTypesPanel(
        Application $application,
        #[MapEntity(mapping: ['parentMenu' => 'uuid'])]
        Menu $parentMenu
    ): JsonResponse
    {
        $allPageTypes = $this->pageTypeService->getAllPageTypes();
     $html = $this->render('page/pages_types_panel.html.twig', [
         'pageTypes' => $allPageTypes,
         'parentMenu' => $parentMenu,
     ])->getContent();
        return new JsonResponse(['content' => $html]);
    }

    #[Route('/app/menu/{parentMenu}/page/add/{type}', name: 'app_menu_add_page')]
    public function addPage(
        Request $request,
        EntityManagerInterface $em,
        Application $application,
        #[MapEntity(mapping: ['parentMenu' => 'uuid'])]
        Menu $parentMenu,
        string $type
    ): Response
    {

        $pageTypeMetadata = $this->pageTypeService->getPageTypeMetadata($type);
        /** @var ContentPage|Menu $page */
        $page = new $pageTypeMetadata['entity_class']();
        $form = $this->createForm($pageTypeMetadata['form_class'], $page);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $menuItem = new MenuItem();
            $menuItem->setParentMenu($parentMenu);
            $menuItem->setPositionIndex(count($parentMenu->getMenuItems()));
            $page->setMenuItem($menuItem);
            $em->persist($page);
            $em->flush();
            return $this->redirectToRoute('app_structure');
        }

        return $this->render('page/add_page.html.twig', [
            'form' => $form->createView(),
            'parentMenu' => $parentMenu,
            'pageType' => $type,
        ]);
    }

    #[Route('/app/page/{page}/edit', name: 'app_edit_page')]
    public function editPage(
        Request $request,
        EntityManagerInterface $em,
        Application $application,
        Page $page
    ): Response
    {

        return $this->render('page_edit/content_page_edit.html.twig', [
            'page' => $page,
        ]);
    }
}
