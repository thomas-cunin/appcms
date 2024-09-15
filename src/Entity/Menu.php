<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu extends Page
{

    const TYPE_MAIN = 'main';
    const TYPE_UNNASIGNED = 'unassigned';
    const TYPE_SUBMENU = 'submenu';

    #[ORM\Column(length: 255)]
    private ?string $menuType = null;

    /**
     * @var Collection<int, MenuItem>
     */
    #[ORM\OneToMany(targetEntity: MenuItem::class, mappedBy: 'parentMenu', cascade: ['persist'], orphanRemoval: true)]
    private Collection $menuItems;


    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Application $application = null;

    public function __construct()
    {
        parent::__construct();
        $this->menuType = self::TYPE_SUBMENU;
        $this->menuItems = new ArrayCollection();
    }


    public function getMenuType(): ?string
    {
        return $this->menuType;
    }

    public function setMenuType(string $menuType): static
    {
        $this->menuType = $menuType;

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
