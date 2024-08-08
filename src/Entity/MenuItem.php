<?php

namespace App\Entity;

use App\Repository\MenuItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'menuItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $parentMenu = null;

    #[ORM\OneToOne(inversedBy: 'menuItem', cascade: ['persist', 'remove'])]
    private ?Page $page = null;

    #[ORM\OneToOne(inversedBy: 'menuItem', cascade: ['persist', 'remove'])]
    private ?Menu $submenu = null;

    #[ORM\Column]
    private ?int $positionIndex = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    public function __construct()
    {
        $this->uuid = str_replace('-','',Uuid::v4()->toRfc4122());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentMenu(): ?Menu
    {
        return $this->parentMenu;
    }

    public function setParentMenu(?Menu $parentMenu): static
    {
        $this->parentMenu = $parentMenu;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getSubmenu(): ?Menu
    {
        return $this->submenu;
    }

    public function setSubmenu(?Menu $submenu): static
    {
        $this->submenu = $submenu;

        return $this;
    }

    public function getPositionIndex(): ?int
    {
        return $this->positionIndex;
    }

    public function setPositionIndex(int $positionIndex): static
    {
        $this->positionIndex = $positionIndex;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setItem(Page|Menu $item): static
    {
        if ($item instanceof Page) {
            $this->setPage($item);
        } else {
            $this->setSubmenu($item);
        }

        return $this;
    }
    // create getItem method for get Menu or Page
    public function getItem(): Page|Menu
    {
        return $this->page ?? $this->submenu;
    }


}
