<?php

namespace App\Entity;

use App\Repository\ClubStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubStatisticsRepository::class)]
class ClubStatistics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['club_history:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['club_history:read'])]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['club_history:read'])]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'clubStatistics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClubHistory $clubHistory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getClubHistory(): ?ClubHistory
    {
        return $this->clubHistory;
    }

    public function setClubHistory(?ClubHistory $clubHistory): static
    {
        $this->clubHistory = $clubHistory;

        return $this;
    }
}
