<?php

namespace App\Entity;

use App\Repository\MembershipApplicationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembershipApplicationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MembershipApplication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $pdfFilename = null;

    #[ORM\Column(length: 512)]
    private ?string $pdfPath = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPdfFilename(): ?string
    {
        return $this->pdfFilename;
    }

    public function setPdfFilename(string $pdfFilename): static
    {
        $this->pdfFilename = $pdfFilename;

        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(string $pdfPath): static
    {
        $this->pdfPath = $pdfPath;

        return $this;
    }

    public function getPdfToken(): ?string
    {
        if ($this->pdfFilename === null) {
            return null;
        }

        if (preg_match('/^(?:aufnahmeantrag|membership-application)-((?:\d{8}-[a-f0-9]{32}|test))\.pdf$/', $this->pdfFilename, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
