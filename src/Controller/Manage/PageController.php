<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\Menu;
use App\Service\PageTypeService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        Application $application,
        #[MapEntity(mapping: ['parentMenu' => 'uuid'])]
        Menu $parentMenu,
        string $type
    ): Response
    {
        $pageTypeMetadata = $this->pageTypeService->getPageTypeMetadata($type);
        return $this->redirectToRoute('app_menu_edit_page', ['menu' => $parentMenu->getUuid(), 'page' => $page->getUuid()]);
    }

}
