<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Enum\Country;
use App\Repository\AdvertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['adverts:read']],
    denormalizationContext: ['groups' => ['adverts:write']],
  )]
#[Get]
#[GetCollection]
#[Post(security: "is_granted('ROLE_USER')")]
#[Patch(security: "is_granted('ROLE_ADMIN') or object.getOwner() == user")]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.getOwner() == user")]
class Advert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adverts:read', 'users:read', 'booking:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre doit être renseigné.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['adverts:read', 'adverts:write', 'users:read', 'booking:read'])]
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
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\NotBlank(message: 'Le code postal doit être renseigné.')]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9\- ]{2,10}$/',
        message: 'Le format du code postal est incorrect.'
    )] 
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?string $zipCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'La ville doit être renseigné.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?string $city = null;

    #[ORM\Column(type: 'string', enumType: Country::class)]
    #[Assert\NotBlank(message: 'Le pays doit être renseigné.')]
    #[Groups(['adverts:read', 'adverts:write', 'users:read'])]
    private ?Country $country = null;

    #[ORM\Column]
    #[Groups(['adverts:read'])]
    private ?int $nbRoom = 0;

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


    /**
     * @var Collection<int, AdvertImg>
     */
    #[ORM\OneToMany(targetEntity: AdvertImg::class, cascade: ['persist', 'remove'], mappedBy: 'advert', orphanRemoval: true)]
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

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): static
    {
        $this->country = $country;

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

    // Callback PrePersist
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
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

    // Callback PreUpdate
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
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

}
