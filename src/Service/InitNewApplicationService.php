<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\ContentPage;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

class InitNewApplicationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {

    }

    /**
     * Initializes the main menu and unassigned pages menu for a new application.
     *
     * This method is responsible for creating the default main menu and unassigned pages menu for a new application.
     * It checks if the application already has menus, and if not, it creates the main menu and unassigned pages menu,
     * adds the default "Home" and "About" pages to the main menu, and persists the menus to the database.
     *
     * @param Application $application The application to initialize the menus for.
     */
    public function init(Application $application): void
    {
        // Si l'application a déjà des menus, ne pas générer les menus de démarrage
        if ($application->getMainMenu() || $application->getUnassignedPagesMenu()) {
            return;
        }

        $mainMenu = new Menu();
        $mainMenu->setName('Main menu');
        $mainMenu->setSlug('main-menu');
        $mainMenu->setApplication($application);
        $mainMenu->setMenuType(Menu::TYPE_MAIN);

        $unassignedPagesMenu = new Menu();
        $unassignedPagesMenu->setName('Unassigned pages');
        $unassignedPagesMenu->setSlug('unassigned-pages');
        $unassignedPagesMenu->setMenuType(Menu::TYPE_UNNASIGNED);
        $unassignedPagesMenu->setApplication($application);

        
        

        // Page d'accueil
        $page1 = new ContentPage();

        $page1->setName('Home');
        $page1->setSlug('home');

        $mainMenuItem1 = new MenuItem();
        $mainMenuItem1->setPage($page1);
        $mainMenuItem1->setParentMenu($mainMenu);  // Associe le menuItem au menu
        $mainMenuItem1->setPositionIndex(0);

        $mainMenu->addMenuItem($mainMenuItem1);

        // Page À propos
        $page2 = new ContentPage();
        $page2->setName('About');
        $page2->setSlug('about');


        $mainMenuItem2 = new MenuItem();
        $mainMenuItem2->setPage($page2);
        $mainMenuItem2->setParentMenu($mainMenu);  // Associe le menuItem au menu
        $mainMenuItem2->setPositionIndex(1);

        $mainMenu->addMenuItem($mainMenuItem2);

        // Menu Our services
        $menu1 = new Menu();
        $menu1->setName('Our services');
        $menu1->setSlug('our-services');
        $menu1->setMenuType(Menu::TYPE_SUBMENU);
        $menu1->setApplication($application);

        $mainMenuItem3 = new MenuItem();
        $mainMenuItem3->setPage($menu1);
        $mainMenuItem3->setParentMenu($mainMenu);  // Associe le menuItem au menu
        $mainMenuItem3->setPositionIndex(2);

        $mainMenu->addMenuItem($mainMenuItem3);

//        // Page Service 1
//        $page3 = new ContentPage();
//        $page3->setName('Service 1');
//        $page3->setSlug('service-1');
//
//        $menuItem1 = new MenuItem();
//        $menuItem1->setPage($page3);
//        $menuItem1->setParentMenu($menu1);  // Associe le menuItem au menu
//        $menuItem1->setPositionIndex(0);
//
//        $menu1->addMenuItem($menuItem1);

        $this->entityManager->persist($mainMenu);
        $this->entityManager->persist($unassignedPagesMenu);
        $this->entityManager->flush();

    }

}