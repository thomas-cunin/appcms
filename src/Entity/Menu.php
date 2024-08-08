<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{

    const TYPE_MAIN = 'main';
    const TYPE_UNNASIGNED = 'unassigned';
    const TYPE_SUBMENU = 'submenu';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, MenuItem>
     */
    #[ORM\OneToMany(targetEntity: MenuItem::class, mappedBy: 'parentMenu', cascade: ['persist'], orphanRemoval: true)]
    private Collection $menuItems;

    #[ORM\OneToOne(mappedBy: 'submenu', cascade: ['persist', 'remove'])]
    private ?MenuItem $menuItem = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Application $application = null;

    public function __construct()
    {
        $this->uuid = str_replace('-', '', Uuid::v4()->toRfc4122());

        $this->menuItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, MenuItem>
     */
    public function getMenuItems(): Collection
    {
        return $this->menuItems;
    }

    public function addMenuItem(MenuItem $menuItem): static
    {
        if (!$this->menuItems->contains($menuItem)) {
            $this->menuItems->add($menuItem);
            $menuItem->setParentMenu($this);
        }

        return $this;
    }

    public function removeMenuItem(MenuItem $menuItem): static
    {
        if ($this->menuItems->removeElement($menuItem)) {
            // set the owning side to null (unless already changed)
            if ($menuItem->getParentMenu() === $this) {
                $menuItem->setParentMenu(null);
            }
        }

        return $this;
    }

    public function getMenuItem(): ?MenuItem
    {
        return $this->menuItem;
    }

    public function setMenuItem(?MenuItem $menuItem): static
    {
        // unset the owning side of the relation if necessary
        if ($menuItem === null && $this->menuItem !== null) {
            $this->menuItem->setSubmenu(null);
        }

        // set the owning side of the relation if necessary
        if ($menuItem !== null && $menuItem->getSubmenu() !== $this) {
            $menuItem->setSubmenu($this);
        }

        $this->menuItem = $menuItem;

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

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }
}
