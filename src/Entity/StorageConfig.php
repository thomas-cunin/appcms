<?php

namespace App\Entity;

use App\Repository\StorageConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StorageConfigRepository::class)]
class StorageConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $s3BucketName = null;

    #[ORM\Column(length: 255)]
    private ?string $s3AccessKeyId = null;

    #[ORM\Column(length: 255)]
    private ?string $s3SecretAccessKey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getS3BucketName(): ?string
    {
        return $this->s3BucketName;
    }

    public function setS3BucketName(string $s3BucketName): static
    {
        $this->s3BucketName = $s3BucketName;

        return $this;
    }

    public function getS3AccessKeyId(): ?string
    {
        return $this->s3AccessKeyId;
    }

    public function setS3AccessKeyId(string $s3AccessKeyId): static
    {
        $this->s3AccessKeyId = $s3AccessKeyId;

        return $this;
    }

    public function getS3SecretAccessKey(): ?string
    {
        return $this->s3SecretAccessKey;
    }

    public function setS3SecretAccessKey(string $s3SecretAccessKey): static
    {
        $this->s3SecretAccessKey = $s3SecretAccessKey;

        return $this;
    }
}
