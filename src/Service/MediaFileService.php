<?php

namespace App\Service;

use App\Entity\Media;
use Aws\S3\S3Client;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Intervention\Image\ImageManager;

class MediaFileService
{
    private string $uploadDir;
    private ImageManager $imageManager;

    public function __construct(string $projectDir)
    {
        $this->uploadDir = $projectDir . '/uploads';
        $this->imageManager = new ImageManager(new Driver());
    }

    public function uploadFile(UploadedFile $file, string $mediaType): string
    {

        // Générer un nom de fichier unique
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        if (strlen($originalFilename) > 100) {
            $originalFilename = substr($originalFilename, 0, 100);
        }
        $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Upload du fichier vers S3
        $bucket = 'liliathum';
        $s3Key = $mediaType . '/' . $newFilename;

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => $s3Key,
                'SourceFile' => $file->getPathname(),
                'Metadata'   => [
                    'Original-Filename' => $file->getClientOriginalName(),
                    'Media-Type' => $mediaType,
                ],
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to upload file to S3: ' . $e->getMessage());
        }
dump($result);
        // Retourner l'URL de l'objet S3
        return $result['ObjectURL'];
    }

    public function getMedia(string $mediaType, string $filename): string
    {
        $filePath = $this->uploadDir . '/' . $mediaType . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Media not found.');
        }

        return $filePath;
    }

    public function getThumbnail(string $mediaType, string $filename, string $size): string
    {
        $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '-' . $size . '.' . pathinfo($filename, PATHINFO_EXTENSION);
        $thumbnailPath = $this->uploadDir . '/' . $mediaType . '/thumbnails/' . $thumbnailFilename;

        if (!file_exists($thumbnailPath)) {
            throw new NotFoundHttpException('Thumbnail not found.');
        }

        return $thumbnailPath;
    }

    public function deleteMedia(string $mediaType, string $filename): void
    {
        $filesystem = new Filesystem();
        $filePath = $this->getMedia($mediaType, $filename);

        // Delete the main file
        $filesystem->remove($filePath);

        // Delete the thumbnails if they exist
        if ($mediaType === Media::TYPE_IMAGE) {
            $sizes = ['small', 'medium', 'large'];
            foreach ($sizes as $size) {
                $thumbnailPath = $this->uploadDir . '/' . $mediaType . '/thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '-' . $size . '.' . pathinfo($filename, PATHINFO_EXTENSION);
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
            'small' => 150,
            'medium' => 300,
            'large' => 600
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
