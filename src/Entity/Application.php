<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Menu>
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'application', fetch: 'EAGER', orphanRemoval: true)]
    private Collection $menus;

    public function __construct()
    {
        $this->uuid = str_replace('-', '', Uuid::v4()->toRfc4122());
        $this->menus = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

public function getMainMenu(): ?Menu
{
    foreach($this->menus as $menu){
        if($menu->getMenuType() === Menu::TYPE_MAIN){
            return $menu;
        }
    }
    return null;
}

public function getUnassignedPagesMenu(): ?Menu
{
    foreach($this->menus as $menu){
        if($menu->getMenuType() === Menu::TYPE_UNNASIGNED){
            return $menu;
        }
    }
    return null;
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setApplication($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getApplication() === $this) {
                $menu->setApplication(null);
            }
        }

        return $this;
    }
}
