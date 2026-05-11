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
    private ?string $membershipApplicationPdfFilename = null;

    #[ORM\Column(length: 512)]
    private ?string $membershipApplicationPdfPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $supervisionDutyPdfFilename = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $supervisionDutyPdfPath = null;

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

    public function getMembershipApplicationPdfFilename(): ?string
    {
        return $this->membershipApplicationPdfFilename;
    }

    public function setMembershipApplicationPdfFilename(string $membershipApplicationPdfFilename): static
    {
        $this->membershipApplicationPdfFilename = $membershipApplicationPdfFilename;

        return $this;
    }

    public function getMembershipApplicationPdfPath(): ?string
    {
        return $this->membershipApplicationPdfPath;
    }

    public function setMembershipApplicationPdfPath(string $membershipApplicationPdfPath): static
    {
        $this->membershipApplicationPdfPath = $membershipApplicationPdfPath;

        return $this;
    }

    public function getSupervisionDutyPdfFilename(): ?string
    {
        return $this->supervisionDutyPdfFilename;
    }

    public function setSupervisionDutyPdfFilename(?string $supervisionDutyPdfFilename): static
    {
        $this->supervisionDutyPdfFilename = $supervisionDutyPdfFilename;

        return $this;
    }

    public function getSupervisionDutyPdfPath(): ?string
    {
        return $this->supervisionDutyPdfPath;
    }

    public function setSupervisionDutyPdfPath(?string $supervisionDutyPdfPath): static
    {
        $this->supervisionDutyPdfPath = $supervisionDutyPdfPath;

        return $this;
    }

    public function getMembershipApplicationPdfToken(): ?string
    {
        if ($this->membershipApplicationPdfFilename === null) {
            return null;
        }

        if (preg_match('/^(?:aufnahmeantrag|membership-application)-((?:\d{8}-[a-f0-9]{32}|test))\.pdf$/', $this->membershipApplicationPdfFilename, $matches) !== 1) {
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
