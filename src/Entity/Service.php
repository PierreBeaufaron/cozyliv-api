<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ApiResource(
    paginationEnabled: false,
    normalizationContext: ['groups' => ['services:read']],
    denormalizationContext: ['groups' => ['services:write']]
  )]
  #[Get(security: "is_granted('ROLE_USER')")]
  #[GetCollection(security: "is_granted('ROLE_USER')")]
  #[Post(security: "is_granted('ROLE_ADMIN')")]
  #[Patch(security: "is_granted('ROLE_ADMIN')")]
  #[Delete(security: "is_granted('ROLE_ADMIN')")]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['services:read', 'adverts:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['services:read', 'services:write', 'adverts:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['services:read', 'services:write', 'adverts:read'])]
    private ?string $icon = null;

    #[ORM\Column]
    #[Groups(['services:read', 'adverts:read'])]
    private ?int $type = null;

    /**
     * @var Collection<int, Advert>
     */
    #[ORM\ManyToMany(targetEntity: Advert::class, mappedBy: 'services')]
    private Collection $adverts;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\ManyToMany(targetEntity: Room::class, mappedBy: 'services')]
    private Collection $rooms;

    public function __construct()
    {
        $this->adverts = new ArrayCollection();
        $this->rooms = new ArrayCollection();
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Advert>
     */
    public function getAdverts(): Collection
    {
        return $this->adverts;
    }

    public function addAdvert(Advert $advert): static
    {
        if (!$this->adverts->contains($advert)) {
            $this->adverts->add($advert);
            $advert->addService($this);
        }

        return $this;
    }

    public function removeAdvert(Advert $advert): static
    {
        if ($this->adverts->removeElement($advert)) {
            $advert->removeService($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->addService($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            $room->removeService($this);
        }

        return $this;
    }
}
