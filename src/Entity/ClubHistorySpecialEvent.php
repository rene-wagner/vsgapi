<?php

namespace App\Entity;

use App\Repository\ClubHistorySpecialEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubHistorySpecialEventRepository::class)]
class ClubHistorySpecialEvent
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
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['club_history:read'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['club_history:read'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'specialEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClubHistory $clubHistory = null;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
