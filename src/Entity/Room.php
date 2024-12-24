<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['rooms:read']],
    denormalizationContext: ['groups' => ['rooms:write']]
  )]
#[Get]
#[GetCollection]
#[Post(securityPostDenormalize: "is_granted('ROOM_CREATE', object)")]
#[Patch(security: "is_granted('ROLE_ADMIN') or object.getAdvert().getOwner() == user")]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.getAdvert().getOwner() == user")]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rooms:read', 'adverts:read', 'booking:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'Un nom doit être renseigné.')]
    #[Assert\Length(
        min: 3,
        max: 80,
        minMessage: 'Le nom de la chambre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom de la chambre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Groups(['rooms:read', 'rooms:write', 'adverts:read', 'booking:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(
        min: 0,
        max: 9999,
        notInRangeMessage: 'Le prix doit être compris entre {{ min }} et {{ max }}.',
    )]
    #[Groups(['rooms:read', 'rooms:write', 'adverts:read', 'booking:read'])]
    private ?int $rentPrice = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La surface doit être renseignée.')]
    #[Assert\Positive(message: 'La surface doit être supérieure à 0.')]
    #[Groups(['rooms:read', 'rooms:write', 'adverts:read'])]
    private ?float $surfaceArea = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true, writableLink: true)]
    #[Groups(['rooms:read', 'rooms:write', 'booking:read'])]
    private ?Advert $advert = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'room', orphanRemoval: true)]
    #[Groups(['adverts:read'])]
    private Collection $bookings;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'rooms')]
    private Collection $services;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->services = new ArrayCollection();
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

    public function getRentPrice(): ?int
    {
        return $this->rentPrice;
    }

    public function setRentPrice(int $rentPrice): static
    {
        $this->rentPrice = $rentPrice;

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

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): static
    {
        $this->advert = $advert;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setRoom($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getRoom() === $this) {
                $booking->setRoom(null);
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
