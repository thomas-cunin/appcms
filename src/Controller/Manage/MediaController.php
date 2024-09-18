<?php

namespace App\Controller\Manage;

use App\Entity\Media;
use App\Service\MediaFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    #[Route('/test-upload', name: 'test_media_upload')]
    public function testUpload(Request $request, MediaFileService $mediaFileService,EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('file');
            $mediaType = Media::TYPE_IMAGE; // Vous pouvez le rendre dynamique

            if ($file) {
                $media = $mediaFileService->uploadFile($file, $mediaType);

                // Sauvegardez l'entité Media dans la base de données si nécessaire

//                $entityManager->persist($media);
//                $entityManager->flush();
                return new Response('File uploaded successfully with ID ');
            }

            return new Response('No file uploaded', 400);
        }

        return new Response(
            '<form method="POST" enctype="multipart/form-data">
                <input type="file" name="file" />
                <button type="submit">Upload</button>
            </form>'
        );
    }
}
