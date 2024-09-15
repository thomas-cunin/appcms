<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\ContentPage;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\Page;
use App\Form\DeletePageType;
use App\Form\PageSettingsType;
use App\Service\PageTypeService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Type;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    ): JsonResponse
    {

        $pageTypeMetadata = $this->pageTypeService->getPageTypeMetadata($type);
        /** @var ContentPage|Menu $page */
        $page = new $pageTypeMetadata['entity_class']();
        $form = $this->createForm($pageTypeMetadata['form_class'], $page, [
            'action' => $this->generateUrl('app_menu_add_page', ['parentMenu' => $parentMenu->getUuid(), 'type' => $type]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $menuItem = new MenuItem();
            $menuItem->setParentMenu($parentMenu);
            $menuItem->setPositionIndex(count($parentMenu->getMenuItems()));
            $page->setMenuItem($menuItem);
            $em->persist($page);
            $em->flush();
            return new JsonResponse(['success' => true, 'newData' => [
                'type' => $page->getType(),
                'name' => $page->getName(),
                'uuid' => $page->getUuid(),
            ]]);
        }

        return new JsonResponse([
            'content' => $this->render('page/_add_page.html.twig', [
                'form' => $form->createView(),
                'parentMenu' => $parentMenu,
                'pageType' => $type,
            ])->getContent(),
        ]);
    }

    #[Route('/app/page/{page}/edit', name: 'app_edit_page')]
    public function editPage(
        Request $request,
        EntityManagerInterface $em,
        Application $application,
        #[MapEntity(mapping: ['page' => 'uuid'])]
        Page $page
    ): Response
    {

        return $this->render('page_edit/content_page_edit.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/app/page/{page}/settings/edit', name: 'app_edit_page_settings')]
    public function editPageSettings(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        Application $application,
        #[MapEntity(mapping: ['page' => 'uuid'])]
        Page $page
    ): JsonResponse
    {
        $form = $this->createForm(PageSettingsType::class, $page, [
            'action' => $this->generateUrl('app_edit_page_settings', ['page' => $page->getUuid()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($page);
            $em->flush();
            return new JsonResponse(['success' => true,
                'updatedData' => [
                    "type" => $page->getType(),
                    'name' => $page->getName(),
                    'uuid' => $page->getUuid(),
                ],
            ]);
        } elseif ($form->isSubmitted()) {
            return new JsonResponse([
                'success' => false,
                'content' => $this->render('page_edit/page_settings_edit.html.twig', [
                    'form' => $form->createView(),
                    'page' => $page,
                ])->getContent(),
            ]);
        }

        return new JsonResponse([
            'content' => $this->render('page_edit/page_settings_edit.html.twig', [
                'form' => $form->createView(),
                'page' => $page,
            ])->getContent(),
        ]);
    }

    #[Route('/app/page/{page}/delete', name: 'app_delete_page')]
    public function deletePage(
        Request $request,
        EntityManagerInterface $em,
        Application $application,
        #[MapEntity(mapping: ['page' => 'uuid'])]
        Page $page
    ): JsonResponse
    {
        $keepChildren = $request->get('keepChildren', false);
        dump($keepChildren);
        $uuid = $page->getUuid();
        $type = $page->getType();
        $name = $page->getName();
        $em->remove($page);
        $em->flush();
        return new JsonResponse(['success' => true, 'oldData' => ['uuid' => $uuid, 'type' => $type, 'name' => $name]]);
        // generate form to confirm deletion
//        $form = $this->createForm(DeletePageType::class, null, [
//            'action' => $this->generateUrl('app_delete_page', ['page' => $page->getUuid()]),
//        ]);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $uuid = $page->getUuid();
//            $type = $page->getType();
//            $name = $page->getName();
//            $em->remove($page);
//            $em->flush();
//            return new JsonResponse(['success' => true, 'oldData' => ['uuid' => $uuid, 'type' => $type, 'name' => $name]]);
//        } elseif ($form->isSubmitted()) {
//            return new JsonResponse([
//                'success' => false,
//                'content' => $this->render('page/delete_page.html.twig', [
//                    'form' => $form->createView(),
//                    'page' => $page,
//                ])->getContent(),
//            ]);
//        }
//
//        return new JsonResponse([
//            'content' => $this->render('page/delete_page.html.twig', [
//                'form' => $form->createView(),
//                'page' => $page,
//            ])->getContent(),
//        ]);



    }
}
