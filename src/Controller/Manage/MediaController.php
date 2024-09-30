<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Service\MediaFileService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    // Route principale de la médiathèque
    #[Route('/media-library', name: 'media_library')]
    public function mediaLibrary(
        Application $application
    ): Response
    {
        return $this->render('media/media_library.html.twig');
    }

    // Route pour la liste paginée des médias
    #[Route('/media-library/list', name: 'media_library_list')]
    public function mediaLibraryList(
        Application $application,
        Request $request,
        MediaRepository $mediaRepository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les paramètres de la requête
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 28);
        $mediaType = $request->query->get('type', null);
        $searchTerm = $request->query->get('search', null);

        // Construire la requête de base
        $queryBuilder = $mediaRepository->createQueryBuilder('m');

        // Ajouter des filtres en fonction des paramètres
        if ($mediaType) {
            $queryBuilder->andWhere('m.type = :type')
                ->setParameter('type', $mediaType);
        }

        if ($searchTerm) {
            $queryBuilder->andWhere('m.originalFileName LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        // Pagination
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $page, /*page number*/
            $limit /*limit per page*/
        );

        // Vérifier si la requête est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->render('media/_media_list.html.twig', [
                'pagination' => $pagination,
            ]);
        }

        // Sinon, rediriger ou afficher une page d'erreur
        return new Response('Cette page n\'est accessible que via AJAX.', 400);
    }

    #[Route('/media-library/upload', name: 'media_library_upload', methods: ['POST'])]
    public function uploadMedia(
        Application $application,
        Request $request,
        MediaFileService $mediaFileService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $files = $request->files->get('file');
        $mediaType = Media::TYPE_IMAGE; // Vous pouvez le rendre dynamique

        if ($files) {
            foreach ($files as $file) {
                $media = $mediaFileService->uploadFile($application, $file, $mediaType);
                $entityManager->persist($media);
            }
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Fichiers uploadés avec succès.']);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Aucun fichier sélectionné.'], 400);
        }
    }

}
