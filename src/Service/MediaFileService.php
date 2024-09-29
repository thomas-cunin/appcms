<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Media;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Intervention\Image\ImageManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MediaFileService
{
    private string $uploadDir;
    private ImageManager $imageManager;

    public function __construct(string $projectDir,private RouterInterface $router)
    {
        $this->uploadDir = $projectDir . '/uploads';
        $this->imageManager = new ImageManager(new Driver());
    }

    public function uploadFile(Application $application,UploadedFile $file, string $mediaType): Media
    {
        $filesystem = new Filesystem();

        // Validate media type
        $validTypes = [Media::TYPE_IMAGE, Media::TYPE_VIDEO, Media::TYPE_AUDIO, Media::TYPE_DOCUMENT];
        if (!in_array($mediaType, $validTypes)) {
            throw new \InvalidArgumentException('Invalid media type.');
        }

        // Create directories if they don't exist
        $destination = $this->uploadDir . '/' . $application->getUuid() . '/' . $mediaType;
        if (!$filesystem->exists($destination)) {
            $filesystem->mkdir($destination, 0777);
        }

        // Generate a unique filename, truncate if original name is too long
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        if (strlen($originalFilename) > 100) {
            $originalFilename = substr($originalFilename, 0, 100);
        }
        $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

// Generate the URL for the media
        $mediaUrl = $this->router->generate('app_asset', [
            'appUuid' => $application->getUuid(),
            'assetType' => $mediaType,
            'fileName' => $newFilename
        ], UrlGeneratorInterface::RELATIVE_PATH);

        // Create a new Media entity
        $media = new Media();
        $media->setOriginalFileName($file->getClientOriginalName())
            ->setType($mediaType)
            ->setOrigin($mediaUrl)
            ->setExtension($file->guessExtension())
            ->setFileName($newFilename)
            ->setUploadedAt(new \DateTimeImmutable());
// Move the file to the target directory
        $file->move($destination, $newFilename);
        // Generate thumbnails if the media type is an image
        if ($mediaType === Media::TYPE_IMAGE) {
            $this->generateThumbnails($destination, $newFilename);
        }

        return $media;
    }

    public function getMedia(Application $application, string $mediaType, string $filename): string
    {
        $filePath = $this->uploadDir . '/' . $application->getUuid() . '/' . $mediaType . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Media not found.');
        }

        return $filePath;
    }

    public function getMediaOptimized(string $appUuid, string $assetType, string $fileName): string
    {
        $filePath = $this->uploadDir . '/' . $appUuid . '/' . $assetType . '/' . $fileName;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Media not found.');
        }

        return $filePath;
    }

    public function getThumbnail(Application $application, string $mediaType, string $filename, string $size): string
    {
        $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '-' . $size . '.' . pathinfo($filename, PATHINFO_EXTENSION);
        $thumbnailPath = $this->uploadDir . '/' . $application->getUuid() . '/' . $mediaType . '/thumbnails/' . $thumbnailFilename;

        if (!file_exists($thumbnailPath)) {
            throw new NotFoundHttpException('Thumbnail not found.');
        }

        return $thumbnailPath;
    }

    public function getMediaOptimizedThumbnail(string $appUuid, string $assetType, string $fileName): string
    {
        $thumbnailFilename = pathinfo($fileName, PATHINFO_FILENAME) . '-medium.' . pathinfo($fileName, PATHINFO_EXTENSION);
        $thumbnailPath = $this->uploadDir . '/' . $appUuid . '/' . $assetType . '/thumbnails/' . $thumbnailFilename;

        if (!file_exists($thumbnailPath)) {
            throw new NotFoundHttpException('Thumbnail not found.');
        }

        return $thumbnailPath;
    }

    public function deleteMedia(Application $application, string $mediaType, string $filename): void
    {
        $filesystem = new Filesystem();
        $filePath = $this->getMedia($application, $mediaType, $filename);

        // Suppression du fichier principal
        $filesystem->remove($filePath);

        // Suppression des thumbnails si existants
        if ($mediaType === Media::TYPE_IMAGE) {
            $sizes = ['medium'];
            foreach ($sizes as $size) {
                $thumbnailPath = $this->uploadDir . '/' . $application->getUuid() . '/' . $mediaType . '/thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '-' . $size . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                if ($filesystem->exists($thumbnailPath)) {
                    $filesystem->remove($thumbnailPath);
                }
            }
        }
    }

    private function generateThumbnails(string $directory, string $filename): void
    {
        $thumbnailDir = $directory . '/thumbnails';
        $filesystem = new Filesystem();

        if (!$filesystem->exists($thumbnailDir)) {
            $filesystem->mkdir($thumbnailDir, 0777);
        }

        $sizes = [
            'medium' => 300,
        ];

        foreach ($sizes as $sizeName => $width) {
            $image = $this->imageManager->read($directory . '/' . $filename);
            $imageRatio = $image->width() / $image->height();
            $newHeight = $width / $imageRatio;

            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '-' . $sizeName . '.' . pathinfo($filename, PATHINFO_EXTENSION);
            $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;

            // Resize the image to the specified width and constrain aspect ratio
            $image->resize($width, $newHeight, function ($constraint) {
                $constraint->aspectRatio();
            });

            // Save the thumbnail
            $image->save($thumbnailPath);
        }
    }
}
