<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\MediaItem;
use App\Enum\EventRecurrence;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['event:read']],
    order: ['startsAt' => 'ASC'],
)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['event:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $location = null;

    #[ORM\Column(enumType: EventRecurrence::class, nullable: true)]
    #[Groups(['event:read'])]
    private ?EventRecurrence $recurrence = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $recurrenceUntil = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['event:read'])]
    private ?MediaItem $picture = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getRecurrence(): ?EventRecurrence
    {
        return $this->recurrence;
    }

    public function setRecurrence(?EventRecurrence $recurrence): static
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    public function getRecurrenceUntil(): ?\DateTimeImmutable
    {
        return $this->recurrenceUntil;
    }

    public function setRecurrenceUntil(?\DateTimeImmutable $recurrenceUntil): static
    {
        $this->recurrenceUntil = $recurrenceUntil;

        return $this;
    }

    public function getPicture(): ?MediaItem
    {
        return $this->picture;
    }

    public function setPicture(?MediaItem $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->startsAt === null || $this->endsAt === null) {
            return;
        }

        if ($this->endsAt <= $this->startsAt) {
            $context->buildViolation('Das Ende muss nach dem Start liegen.')
                ->atPath('endsAt')
                ->addViolation();
        }

        if ($this->startsAt->format('Y-m-d') !== $this->endsAt->format('Y-m-d')) {
            $context->buildViolation('Start und Ende müssen am selben Tag liegen.')
                ->atPath('endsAt')
                ->addViolation();
        }
    }

    #[Assert\Callback]
    public function validateRecurrenceUntil(ExecutionContextInterface $context): void
    {
        if ($this->recurrenceUntil === null || $this->startsAt === null) {
            return;
        }

        if ($this->recurrenceUntil < $this->startsAt) {
            $context->buildViolation('Das Wiederholungsende muss am oder nach dem Startdatum liegen.')
                ->atPath('recurrenceUntil')
                ->addViolation();
        }
    }
}