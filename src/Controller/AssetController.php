<?php

namespace App\Controller;

use App\Service\MediaFileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AssetController extends AbstractController
{

    public function __construct(private MediaFileService $mediaFileService)
    {

    }

    #[Route('/asset/{appUuid}/{assetType}/{fileName}', name: 'app_asset')]
    public function index(string $appUuid, string $assetType, string $fileName): Response
    {
        try {
            $filePath = $this->mediaFileService->getMediaOptimized($appUuid, $assetType, $fileName);

            return $this->file($filePath,null,ResponseHeaderBag::DISPOSITION_INLINE);
        } catch (NotFoundHttpException $e) {
            throw $this->createNotFoundException('Media not found.');
        }
    }

    // a route for get tumbnail of image or video
    #[Route('/asset/{appUuid}/{assetType}/{fileName}/thumbnail', name: 'app_asset_thumbnail')]
    public function thumbnail(string $appUuid, string $assetType, string $fileName): Response
    {
        try {
            $filePath = $this->mediaFileService->getMediaOptimizedThumbnail($appUuid, $assetType, $fileName);

            return $this->file($filePath,null,ResponseHeaderBag::DISPOSITION_INLINE);
        } catch (NotFoundHttpException $e) {
            throw $this->createNotFoundException('Media not found.');
        }
    }
}
