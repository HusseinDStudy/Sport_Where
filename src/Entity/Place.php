<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as GForAnnotations;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href=@Hateoas\Route(
 *     "places.get",
 *     parameters={
 *      "idPlace" = "expr(object.getId())"
 *     }),
 *     exclusion = @Hateoas\Exclusion(groups="getPlace")
 *
 * )
 */

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Une place doit avoir un nom")]
    #[Assert\NotNull()]
    #[Assert\Length(min: 3, minMessage: "Le nom de la place doit faire plus de {{ limit }} caracteres")]
    #[ORM\Column(length: 255)]
    #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
    private ?string $placeName = null;

    #[Assert\NotBlank(message: "Une place doit avoir une addresse")]
    #[Assert\NotNull()]
    #[ORM\Column(length: 255)]
    #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
    private ?string $placeAddress = null;

    #[Assert\NotBlank(message: "Une place doit avoir une ville")]
    #[Assert\NotNull()]
    #[ORM\Column(length: 255)]
    #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
    private ?string $placeCity = null;

    #[Assert\NotBlank(message: "Une place doit avoir un departement")]
    #[Assert\NotNull()]
    #[ORM\Column]
    #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
    private ?int $dept = null;

    #[Assert\NotBlank(message: "Une place doit avoir un type")]
    #[Assert\NotNull()]
    #[ORM\Column(length: 255)]
    #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
    private ?string $placeType = null;

     #[ORM\Column(nullable: true)]
     #[Groups(['getPlace', 'getCoach', 'createUpdatePlace'])]
     #[GForAnnotations(['getPlace', 'getCoach', 'createUpdatePlace'])]
     private ?int $placeRate = null;

    #[Assert\NotBlank(message: "Une place doit avoir un statut")]
    #[Assert\NotNull()]
    #[Assert\Choice(choices: ['ON', 'OFF'], message: 'Le statut doit etre ON ou OFF')]
    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'coachName')]
    #[Groups(['getPlace', 'getCoach','createUpdatePlace'])]
    #[GForAnnotations(['getPlace', 'getCoach','createUpdatePlace'])]
    private ?Coach $coach = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaceName(): ?string
    {
        return $this->placeName;
    }

    public function setPlaceName(string $placeName): self
    {
        $this->placeName = $placeName;

        return $this;
    }

    public function getPlaceAddress(): ?string
    {
        return $this->placeAddress;
    }

    public function setPlaceAddress(string $placeAddress): self
    {
        $this->placeAddress = $placeAddress;

        return $this;
    }

    public function getPlaceCity(): ?string
    {
        return $this->placeCity;
    }

    public function setPlaceCity(string $placeCity): self
    {
        $this->placeCity = $placeCity;

        return $this;
    }

    public function getPlaceType(): ?string
    {
        return $this->placeType;
    }

    public function setPlaceType(string $placeType): self
    {
        $this->placeType = $placeType;

        return $this;
    }

     public function getPlaceRate(): ?int
     {
         return $this->placeRate;
     }

     public function setPlaceRate(?int $placeRate): self
     {
         $this->placeRate = $placeRate;

         return $this;
     }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getDept(): ?int
    {
        return $this->dept;
    }

    public function setDept(int $dept): self
    {
        $this->dept = $dept;

        return $this;
    }
}
