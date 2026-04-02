<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Dieser Slug wird bereits verwendet.')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['department:read']],
)]
#[ApiFilter(PropertyFilter::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    #[Groups(['department:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['department:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[ApiProperty(identifier: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Der Slug darf nur Kleinbuchstaben, Zahlen und Bindestriche enthalten.',
    )]
    #[Groups(['department:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['department:read'])]
    private ?string $description = null;

    /** @var Collection<int, DepartmentStatistic> */
    #[ORM\OneToMany(targetEntity: DepartmentStatistic::class, mappedBy: 'department', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['department:read'])]
    private Collection $departmentStats;

    /** @var Collection<int, DepartmentTrainingGroup> */
    #[ORM\OneToMany(targetEntity: DepartmentTrainingGroup::class, mappedBy: 'department', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Groups(['department:read'])]
    private Collection $trainingGroups;

    public function __construct()
    {
        $this->departmentStats = new ArrayCollection();
        $this->trainingGroups = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, DepartmentStatistic> */
    public function getDepartmentStats(): Collection
    {
        return $this->departmentStats;
    }

    /** @param iterable<DepartmentStatistic> $departmentStats */
    public function setDepartmentStats(iterable $departmentStats): static
    {
        foreach ($this->departmentStats->toArray() as $existing) {
            $this->removeDepartmentStatistic($existing);
        }
        foreach ($departmentStats as $statistic) {
            $this->addDepartmentStatistic($statistic);
        }

        return $this;
    }

    public function addDepartmentStatistic(DepartmentStatistic $statistic): static
    {
        if (!$this->departmentStats->contains($statistic)) {
            $this->departmentStats->add($statistic);
            $statistic->setDepartment($this);
        }

        return $this;
    }

    public function removeDepartmentStatistic(DepartmentStatistic $statistic): static
    {
        $this->departmentStats->removeElement($statistic);

        return $this;
    }

    /** @return Collection<int, DepartmentTrainingGroup> */
    public function getTrainingGroups(): Collection
    {
        return $this->trainingGroups;
    }

    /** @param iterable<DepartmentTrainingGroup> $trainingGroups */
    public function setTrainingGroups(iterable $trainingGroups): static
    {
        foreach ($this->trainingGroups->toArray() as $existing) {
            $this->removeTrainingGroup($existing);
        }
        foreach ($trainingGroups as $trainingGroup) {
            $this->addTrainingGroup($trainingGroup);
        }

        return $this;
    }

    public function addTrainingGroup(DepartmentTrainingGroup $trainingGroup): static
    {
        if (!$this->trainingGroups->contains($trainingGroup)) {
            $this->trainingGroups->add($trainingGroup);
            $trainingGroup->setDepartment($this);
        }

        return $this;
    }

    public function removeTrainingGroup(DepartmentTrainingGroup $trainingGroup): static
    {
        $this->trainingGroups->removeElement($trainingGroup);

        return $this;
    }
}
