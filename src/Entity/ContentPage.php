<?php

namespace App\Entity;

use App\Repository\ContentPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Page;

#[ORM\Entity(repositoryClass: ContentPageRepository::class)]
class ContentPage extends Page
{

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    public function __construct()
    {
        parent::__construct();
        $this->content= '';
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
