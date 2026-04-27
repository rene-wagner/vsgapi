<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ContentBlockRepository;
use App\State\ContentBlockUpsertProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContentBlockRepository::class)]
#[ORM\Table(
    name: 'content_block',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'content_block_uidx_id_url', columns: ['id', 'url'])],
    indexes: [
        new ORM\Index(name: 'content_block_idx_id', columns: ['id']),
        new ORM\Index(name: 'content_block_idx_url', columns: ['url']),
    ],
)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            stateless: false,
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            processor: ContentBlockUpsertProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['content_block:read']],
    denormalizationContext: ['groups' => ['content_block:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['url' => 'exact'])]
class ContentBlock
{
    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $pk = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups(['content_block:read', 'content_block:write'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['content_block:read', 'content_block:write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['content_block:read', 'content_block:write'])]
    private ?string $content = null;

    public function getPk(): ?int
    {
        return $this->pk;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(string|Uuid $id): static
    {
        $this->id = $id instanceof Uuid ? $id : Uuid::fromString($id);

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
