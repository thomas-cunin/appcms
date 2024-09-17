<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_DOCUMENT = 'document';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFileName = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $origin = null;

    #[ORM\Column(length: 255)]
    private ?string $extension = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $altAttribute = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): static
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getAltAttribute(): ?string
    {
        return $this->altAttribute;
    }

    public function setAltAttribute(?string $altAttribute): static
    {
        $this->altAttribute = $altAttribute;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
}
