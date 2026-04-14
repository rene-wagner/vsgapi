<?php

namespace App\Entity;

use App\Repository\DepartmentTrainingSessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentTrainingSessionRepository::class)]
class DepartmentTrainingSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['department:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['department:read'])]
    private ?string $day = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['department:read'])]
    private ?string $time = null;

    #[ORM\ManyToOne(inversedBy: 'departmentTrainingSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DepartmentTrainingGroup $departmentTrainingGroup = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['department:read'])]
    private ?Location $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getDepartmentTrainingGroup(): ?DepartmentTrainingGroup
    {
        return $this->departmentTrainingGroup;
    }

    public function setDepartmentTrainingGroup(?DepartmentTrainingGroup $departmentTrainingGroup): static
    {
        $this->departmentTrainingGroup = $departmentTrainingGroup;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }
}
