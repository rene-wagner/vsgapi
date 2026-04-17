<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\LocationRepository;
use App\Entity\MediaItem;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['location:read']],
)]
#[ApiFilter(PropertyFilter::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['location:read', 'department:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['location:read', 'department:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['location:read', 'department:read'])]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['location:read', 'department:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Assert\Length(max: 2048)]
    #[Groups(['location:read', 'department:read'])]
    private ?string $mapsUrl = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['location:read', 'department:read'])]
    private ?MediaItem $picture = null;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getMapsUrl(): ?string
    {
        return $this->mapsUrl;
    }

    public function setMapsUrl(?string $mapsUrl): static
    {
        $this->mapsUrl = $mapsUrl;

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
    public function validateMapsUrl(ExecutionContextInterface $context): void
    {
        if ($this->mapsUrl === null || $this->mapsUrl === '') {
            return;
        }
        $context->getValidator()
            ->inContext($context)
            ->atPath('mapsUrl')
            ->validate($this->mapsUrl, new Assert\Url());
    }
}
