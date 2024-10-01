<?php

namespace App\Controller\Manage;

use App\Entity\Application;
use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Service\MediaFileService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    // Route principale de la médiathèque
    #[Route('/media-library', name: 'media_library')]
    public function mediaLibrary(
        Application $application,
        ParameterBagInterface $params
    ): Response
    {
        $mediaLibraryConfig = $params->get('media_library');

        // Récupérer les types de médias configurés
        $mediaTypes = $mediaLibraryConfig['media_types'];
        return $this->render('media/media_library.html.twig',
            ['mediaTypes' => $mediaTypes]
        );
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
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ): JsonResponse {
        $files = $request->files->get('file');

        $mediaLibraryConfig = $params->get('media_library');
        $mediaTypesConfig = $mediaLibraryConfig['media_types'];

        $results = [];
        if ($files) {
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $fileResult = ['name' => $file->getClientOriginalName()];

                // Récupérer le type MIME du fichier
                $mimeType = $file->getClientMimeType();

                $mediaType = null;
                $constraints = [];
                $mediaTypeConstant = null;

                // Trouver le type de média correspondant au type MIME du fichier
                foreach ($mediaTypesConfig as $typeName => $typeConfig) {
                    $typeConstraints = $typeConfig['constraints'];
                    $mimeTypes = [];
                    $maxSizeConstraint = null;

                    foreach ($typeConstraints as $constraint) {
                        if ($constraint['name'] === 'mimeTypes') {
                            $mimeTypes = $constraint['options'];
                        } elseif ($constraint['name'] === 'max') {
                            $maxSizeConstraint = $constraint['options'];
                        }
                    }

                    if (in_array($mimeType, $mimeTypes)) {
                        $mediaType = $typeName;
                        $constraints['max'] = $maxSizeConstraint;
                        $constraints['mimeTypes'] = $mimeTypes;
                        $mediaTypeConstant = $typeConfig['constant'];
                        break;
                    }
                }

                if (!$mediaType) {
                    // Le type MIME du fichier n'est pas autorisé
                    $fileResult['status'] = 'error';
                    $fileResult['message'] = 'Type de fichier invalide.';
                    $results[] = $fileResult;
                    continue;
                }

                // Vérifier la contrainte de taille maximale
                if (isset($constraints['max'])) {
                    $maxSize = $this->convertToBytes($constraints['max']);
                    if ($file->getSize() > $maxSize) {
                        $fileResult['status'] = 'error';
                        $fileResult['message'] = 'La taille du fichier dépasse la limite autorisée.';
                        $results[] = $fileResult;
                        continue;
                    }
                }

                // Procéder à l'upload
                try {
                    $media = $mediaFileService->uploadFile($application, $file, $mediaTypeConstant);
                    $entityManager->persist($media);

                    $fileResult['status'] = 'success';
                    $fileResult['message'] = 'Fichier uploadé avec succès.';
                } catch (\Exception $e) {
                    $fileResult['status'] = 'error';
                    $fileResult['message'] = 'Erreur lors de l\'upload du fichier.';
                }

                $results[] = $fileResult;
            }

            $entityManager->flush();

            return new JsonResponse($results);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Aucun fichier sélectionné.'], 400);
        }
    }

    #[Route('/media-library/delete', name: 'media_library_delete', methods: ['POST'])]
    public function deleteMedia(
        Application $application,
        Request $request,
        EntityManagerInterface $entityManager,
        MediaRepository $mediaRepository,
        MediaFileService $mediaFileService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $uuids = $data['uuids'] ?? [];

        if (empty($uuids)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Aucun média sélectionné.'], 400);
        }

        foreach ($uuids as $uuid) {
            $media = $mediaRepository->findOneBy(['uuid' => $uuid]);
            if ($media) {
                $mediaFileService->deleteMedia($application, $media->getType(), $media->getFilename());
                $entityManager->remove($media);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Médias supprimés avec succès.']);
    }

    private function convertToBytes($sizeStr): int
    {
        $sizeStr = trim($sizeStr);
        $unit = strtolower($sizeStr[strlen($sizeStr) - 1]);
        $size = (int) $sizeStr;

        return match ($unit) {
            'k' => $size * 1024,
            'm' => $size * 1024 * 1024,
            'g' => $size * 1024 * 1024 * 1024,
            default => $size,
        };
    }
}
