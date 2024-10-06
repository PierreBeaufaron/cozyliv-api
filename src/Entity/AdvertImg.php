<?php

namespace App\Entity;

use App\Repository\AdvertImgRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdvertImgRepository::class)]
class AdvertImg
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adverts:read'])]
    private ?string $url = null;

    #[ORM\Column]
    #[Groups(['adverts:read'])]
    private ?bool $coverImg = null;

    #[ORM\ManyToOne(inversedBy: 'advertImgs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Advert $advert = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function isCoverImg(): ?bool
    {
        return $this->coverImg;
    }

    public function setCoverImg(bool $coverImg): static
    {
        $this->coverImg = $coverImg;

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
}
