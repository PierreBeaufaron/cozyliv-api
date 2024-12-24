<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\BookingRepository;
use App\Validator\BookingDate;
use Doctrine\DBAL\Types\Types;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['booking:read'], 'datetime_format' => 'Y-m-d'],
    denormalizationContext: ['groups' => ['booking:write'], 'datetime_format' => 'Y-m-d'],
  )]
#[Get(security: "is_granted('ROLE_ADMIN') or (object.getTenant() == user) or (object.getRoom().getAdvert().getOwner() == user)")]
#[Post(security: "is_granted('ROLE_USER')")]
#[Patch(security: "is_granted('ROLE_ADMIN') or object.geTenant() == user")]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.getTenant() == user")]
class Booking
{
    #[ORM\Id] 
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking:read', 'users:read', 'adverts:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['booking:read', 'booking:write', 'users:read', 'adverts:read', 'rooms:read'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['booking:read', 'booking:write', 'users:read', 'adverts:read', 'rooms:read'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Groups(['booking:read', 'users:read', 'adverts:read', 'rooms:read'])]
    private ?int $status = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read', 'booking:write', 'users:read'])]
    private ?Room $room = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read', 'adverts:read', 'rooms:read'])]
    private ?User $tenant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking:read', 'adverts:read', 'rooms:read'])]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getTenant(): ?User
    {
        return $this->tenant;
    }

    public function setTenant(?User $tenant): static
    {
        $this->tenant = $tenant;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }
}
