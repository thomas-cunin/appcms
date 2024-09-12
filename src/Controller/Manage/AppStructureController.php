<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Service\InitNewApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppStructureController extends AbstractController
{

    public function __construct(private InitNewApplicationService $initNewApplicationService,private EntityManagerInterface $em)
    {
    }

    /**
     * Initializes a new application.
     *
     * This action is responsible for initializing a new application. It calls the `initNewApplicationService` to perform the initialization, and then redirects the user to the 'app_structure' route.
     *
     * @param Application $application The application to be initialized.
     * @return Response The redirect response to the 'app_structure' route.
     */
    #[Route('/app/init', name: 'app_structure_init')]
    public function init(
        Application $application
    ): Response
    {
        $this->initNewApplicationService->init($application);

        return $this->redirectToRoute('app_structure');
    }

    /**
     * Renders the app structure page, displaying the main menu and unassigned pages menu.
     *
     * @param Application $application The application instance.
     * @return Response The rendered response.
     */
    #[Route('/app/content', name: 'app_structure')]
    public function index(
        Application $application,
        Request $request
    ): Response
    {
        $menuRepository = $this->em->getRepository(Menu::class);
        $allMenus = $menuRepository->findBy(['application' => $application]);
        return $this->render('app_structure/index.html.twig', [
            'mainMenu' => $application->getMainMenu(),
            'unassignedPagesMenu' => $application->getUnassignedPagesMenu(),
            'application' => $application,
        ]);
    }

    /**
     * Updates the order of a menu item within a menu.
     *
     * @param Application $application The application instance.
     * @param Request $request The request object.
     * @return JsonResponse The JSON response.
     */
    #[Route('/app/menu/update', name: 'app_structure_menu_update')]
    public function updateMenu(
        Application $application,
        Request $request
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $menuItemUUID = $data['menuItem'] ?? null;
        $parentMenuUUID = $data['parentMenu'] ?? null;
        $positionIndex = $data['positionIndex'];

        /** @var MenuItem $menuItem */
        $menuItem = $this->em->getRepository(MenuItem::class)->findOneBy(['uuid' => $menuItemUUID]);
        /** @var Menu $parentMenu */
        $parentMenu = $this->em->getRepository(Menu::class)->findOneBy(['uuid' => $parentMenuUUID]);
        if (!$menuItem || !$parentMenu || !is_numeric($positionIndex)) {
            return new JsonResponse(['success' => false, 'message' => 'Menu item or parent menu not found.'], 404);
        }

        // Update the menu item
        if ($menuItem->getParentMenu() !== $parentMenu) {
            $menuItem->setParentMenu($parentMenu);
        }
        $oldPositionIndex = $menuItem->getPositionIndex();
        $menuItem->setPositionIndex($positionIndex);

        $this->em->persist($menuItem);
        dump($menuItem);
        $this->em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Menu order updated successfully.']);
    }

}
