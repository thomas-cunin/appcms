<?php

namespace App\Controller\Manage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApplicationSettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_global_settings')]
    public function index(): Response
    {
        return $this->render('application_settings/pages_types_panel.html.twig', [
            'controller_name' => 'ApplicationSettingsController',
        ]);
    }
}
