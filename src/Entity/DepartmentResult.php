<?php

namespace App\Entity;

use App\Repository\DepartmentResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentResultRepository::class)]
class DepartmentResult
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
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['department:read'])]
    private ?string $league = null;

    #[ORM\Column(length: 2048)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 2048)]
    #[Assert\Url]
    #[Groups(['department:read'])]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'departmentResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

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

    public function getLeague(): ?string
    {
        return $this->league;
    }

    public function setLeague(string $league): static
    {
        $this->league = $league;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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
}
