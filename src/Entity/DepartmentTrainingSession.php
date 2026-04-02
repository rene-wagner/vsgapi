<?php

namespace App\Entity;

use App\Repository\DepartmentTrainingSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /** @var Collection<int, Location> */
    #[ORM\ManyToMany(targetEntity: Location::class)]
    #[ORM\JoinTable(name: 'department_training_session_location')]
    #[Groups(['department:read'])]
    private Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

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

    /** @return Collection<int, Location> */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    /** @param iterable<Location> $locations */
    public function setLocations(iterable $locations): static
    {
        foreach ($this->locations->toArray() as $existing) {
            $this->removeLocation($existing);
        }
        foreach ($locations as $location) {
            $this->addLocation($location);
        }

        return $this;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        $this->locations->removeElement($location);

        return $this;
    }
}
