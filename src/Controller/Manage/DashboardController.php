<?php

namespace App\Controller\Manage;

use App\DataTable\PageTableType;
use App\Entity\MenuItem;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Application;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        Application $application
    ): Response
    {
        return $this->render('dashboard/pages_types_panel.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/pages', name: 'app_pages')]
    public function pages(DataTableFactory $dataTableFactory,Request $request): Response
    {
        $table = $dataTableFactory->createFromType(PageTableType::class);

        $table->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('pages/index.html.twig', [
            'datatable' => $table,
        ]);
    }
}
