<?php

namespace App\Entity;

use App\Repository\RatePlacesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatePlacesRepository::class)]
class RatePlaces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rate = null;

    #[ORM\ManyToOne(inversedBy: 'ratePlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $idPlace = null;

    #[ORM\ManyToOne(inversedBy: 'ratePlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getIdPlace(): ?Place
    {
        return $this->idPlace;
    }

    public function setIdPlace(?Place $idPlace): self
    {
        $this->idPlace = $idPlace;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }
}
