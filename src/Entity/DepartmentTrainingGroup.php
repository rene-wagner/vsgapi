<?php

namespace App\Entity;

use App\Repository\DepartmentTrainingGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentTrainingGroupRepository::class)]
class DepartmentTrainingGroup
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
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['department:read'])]
    private ?string $ageRange = null;

    #[ORM\ManyToOne(inversedBy: 'trainingGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    /** @var Collection<int, DepartmentTrainingSession> */
    #[ORM\OneToMany(targetEntity: DepartmentTrainingSession::class, mappedBy: 'departmentTrainingGroup', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['day' => 'ASC', 'time' => 'ASC'])]
    #[Groups(['department:read'])]
    private Collection $departmentTrainingSessions;

    public function __construct()
    {
        $this->departmentTrainingSessions = new ArrayCollection();
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

    public function getAgeRange(): ?string
    {
        return $this->ageRange;
    }

    public function setAgeRange(string $ageRange): static
    {
        $this->ageRange = $ageRange;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    /** @return Collection<int, DepartmentTrainingSession> */
    public function getDepartmentTrainingSessions(): Collection
    {
        return $this->departmentTrainingSessions;
    }

    /** @param iterable<DepartmentTrainingSession> $departmentTrainingSessions */
    public function setDepartmentTrainingSessions(iterable $departmentTrainingSessions): static
    {
        foreach ($this->departmentTrainingSessions->toArray() as $existing) {
            $this->removeDepartmentTrainingSession($existing);
        }
        foreach ($departmentTrainingSessions as $session) {
            $this->addDepartmentTrainingSession($session);
        }

        return $this;
    }

    public function addDepartmentTrainingSession(DepartmentTrainingSession $session): static
    {
        if (!$this->departmentTrainingSessions->contains($session)) {
            $this->departmentTrainingSessions->add($session);
            $session->setDepartmentTrainingGroup($this);
        }

        return $this;
    }

    public function removeDepartmentTrainingSession(DepartmentTrainingSession $session): static
    {
        $this->departmentTrainingSessions->removeElement($session);

        return $this;
    }
}
