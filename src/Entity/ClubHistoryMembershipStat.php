<?php

namespace App\Entity;

use App\Repository\ClubHistoryMembershipStatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubHistoryMembershipStatRepository::class)]
class ClubHistoryMembershipStat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['club_history:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1800, max: 3000, notInRangeMessage: 'Bitte geben Sie ein gültiges Jahr ein.')]
    #[Groups(['club_history:read'])]
    private ?int $year = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero(message: 'Die Mitgliederanzahl darf nicht negativ sein.')]
    #[Groups(['club_history:read'])]
    private ?int $memberCount = null;

    #[ORM\ManyToOne(inversedBy: 'membershipStats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClubHistory $clubHistory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMemberCount(): ?int
    {
        return $this->memberCount;
    }

    public function setMemberCount(int $memberCount): static
    {
        $this->memberCount = $memberCount;

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
