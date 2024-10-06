<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AdvertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertRepository::class)]
#[ORM\HasLifecycleCallbacks]  // Indique que cette entité utilise les callbacks du cycle de vie
#[ApiResource(
    normalizationContext: ['groups' => ['adverts:read']],
    denormalizationContext: ['groups' => ['adverts:write']]
  )]
class Advert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adverts:read', 'users:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre doit être renseigné.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description doit être renseignée.')]
    #[Assert\Length(
        min: 144,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères.'
    )]
    #[Groups(['adverts:read', 'adverts:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse doit être renseignée.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['adverts:read', 'adverts:write'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le nombre de pièces doit être renseigné.')]
    #[Assert\Positive(message: 'Le nombre de pièces doit être supérieur à zéro.')]
    #[Groups(['adverts:read', 'adverts:write'])]
    private ?int $nbRoom = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La surface doit être renseignée.')]
    #[Assert\Positive(message: 'La surface doit être supérieure à 0.')]
    #[Groups(['adverts:read', 'adverts:write'])]
    private ?float $surfaceArea = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['adverts:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['adverts:read'])]
    private ?float $rating = null;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'advert', orphanRemoval: true)]
    #[Groups(['adverts:read'])]
    private Collection $rooms;

    #[ORM\ManyToOne(inversedBy: 'adverts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['adverts:read', 'adverts:write'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'adverts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?City $city = null;

    /**
     * @var Collection<int, AdvertImg>
     */
    #[ORM\OneToMany(targetEntity: AdvertImg::class, mappedBy: 'advert', orphanRemoval: true)]
    #[Groups(['adverts:read', 'adverts:write'])]
    private Collection $advertImgs;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'advert', orphanRemoval: true)]
    #[Groups(['adverts:read'])]
    private Collection $reviews;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'adverts')]
    #[Groups(['adverts:read', 'adverts:write'])]
    private Collection $services;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['adverts:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->advertImgs = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getNbRoom(): ?int
    {
        return $this->nbRoom;
    }

    public function setNbRoom(int $nbRoom): static
    {
        $this->nbRoom = $nbRoom;

        return $this;
    }

    public function getSurfaceArea(): ?float
    {
        return $this->surfaceArea;
    }

    public function setSurfaceArea(float $surfaceArea): static
    {
        $this->surfaceArea = $surfaceArea;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // Ajoute cette méthode avec le callback PrePersist
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime(); // Attribue la date actuelle au champ createdAt
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

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
            $room->setAdvert($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getAdvert() === $this) {
                $room->setAdvert(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, AdvertImg>
     */
    public function getAdvertImgs(): Collection
    {
        return $this->advertImgs;
    }

    public function addAdvertImg(AdvertImg $advertImg): static
    {
        if (!$this->advertImgs->contains($advertImg)) {
            $this->advertImgs->add($advertImg);
            $advertImg->setAdvert($this);
        }

        return $this;
    }

    public function removeAdvertImg(AdvertImg $advertImg): static
    {
        if ($this->advertImgs->removeElement($advertImg)) {
            // set the owning side to null (unless already changed)
            if ($advertImg->getAdvert() === $this) {
                $advertImg->setAdvert(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAdvert($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAdvert() === $this) {
                $review->setAdvert(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // Ajout de la méthode pour PreUpdate
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime(); // Attribue la date actuelle lors de la mise à jour
    }

}
