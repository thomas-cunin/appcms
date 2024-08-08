<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'page')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 255)]
#[ORM\DiscriminatorMap(['content' => 'ContentPage'])]
class Page
{

const TYPE_CONTENT = 'content';
const TYPE_FORM = 'form';
const TYPE_HOME = 'home';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

//    private ?string $type = null;

    #[ORM\OneToOne(mappedBy: 'page', cascade: ['persist', 'remove'])]
    private ?MenuItem $menuItem = null;

    public function __construct()
    {
        $this->uuid = str_replace('-', '', Uuid::v4()->toRfc4122());
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

//    public function getType(): ?string
//    {
//        return $this->type;
//    }
//
//    public function setType(string $type): static
//    {
//        $this->type = $type;
//
//        return $this;
//    }
    /**
     * Retrieves the discriminator type of this Page entity.
     * This will only reflect the discriminator type and not any additional logic.
     */
    public function getType(): string
    {
        return array_flip(static::getDiscriminatorMap())[static::class] ?? 'unknown';
    }

    /**
     * Static method to get the discriminator map.
     * Useful if you want to access or validate the types programmatically.
     *
     * @return array
     */
    public static function getDiscriminatorMap(): array
    {
        return [
            self::TYPE_CONTENT => ContentPage::class,
            // Add other mappings here as needed
        ];
    }
    public function getMenuItem(): ?MenuItem
    {
        return $this->menuItem;
    }

    public function setMenuItem(?MenuItem $menuItem): static
    {
        // unset the owning side of the relation if necessary
        if ($menuItem === null && $this->menuItem !== null) {
            $this->menuItem->setPage(null);
        }

        // set the owning side of the relation if necessary
        if ($menuItem !== null && $menuItem->getPage() !== $this) {
            $menuItem->setPage($this);
        }

        $this->menuItem = $menuItem;

        return $this;
    }
}
