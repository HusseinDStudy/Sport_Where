<?php

namespace App\Entity;

use App\Repository\CoachRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
//use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href=@Hateoas\Route(
 *     "coach.get",
 *     parameters={
 *      "idCoach" = "expr(object.getId())"
 *     }),
 *     exclusion = @Hateoas\Exclusion(groups="getCoach")
 *
 * )
 */

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'coach', targetEntity: Place::class)]
    private Collection $coachName;

    #[Assert\NotBlank(message: "Un coach doit avoir un numéro de téléphone")]
    #[Assert\NotNull()]
    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['getPlace', 'getAllPlace','getCoach','getAllCoach'])]
    private ?string $coachPhoneNumber = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[Assert\NotBlank(message: "Un coach doit avoir un nom")]
    #[Assert\NotNull()]
    #[Assert\Length(min: 3, minMessage: "Le nom du coach doit faire plus de {{ limit }} caractères")]
    #[ORM\Column(length: 255)]
    #[Groups(['getPlace', 'getAllPlace','getCoach','getAllCoach'])]
    private ?string $coachFullName = null;

    public function __construct()
    {
        $this->coachName = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Place>
     */
    public function getCoachName(): Collection
    {
        return $this->coachName;
    }

    public function addCoachName(Place $coachName): self
    {
        if (!$this->coachName->contains($coachName)) {
            $this->coachName->add($coachName);
            $coachName->setCoach($this);
        }

        return $this;
    }

    public function removeCoachName(Place $coachName): self
    {
        if ($this->coachName->removeElement($coachName)) {
            // set the owning side to null (unless already changed)
            if ($coachName->getCoach() === $this) {
                $coachName->setCoach(null);
            }
        }

        return $this;
    }

    public function getCoachPhoneNumber(): ?string
    {
        return $this->coachPhoneNumber;
    }

    public function setCoachPhoneNumber(?string $coachPhoneNumber): self
    {
        $this->coachPhoneNumber = $coachPhoneNumber;

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

    public function getCoachFullName(): ?string
    {
        return $this->coachFullName;
    }

    public function setCoachFullName(string $coachFullName): self
    {
        $this->coachFullName = $coachFullName;

        return $this;
    }
}
