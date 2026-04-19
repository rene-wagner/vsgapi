<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\MediaItemType;
use App\Repository\MediaItemRepository;
use App\Controller\Api\MediaItemCopyController;
use App\Controller\Api\MediaItemUploadController;
use App\State\MediaItemDeleteProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/media_items', paginationItemsPerPage: 20, paginationClientItemsPerPage: false),
        new Get(uriTemplate: '/media_items/{id}'),
        new Post(
            uriTemplate: '/media_items/upload',
            controller: MediaItemUploadController::class,
            deserialize: false,
            name: 'media_item_upload',
        ),
        new Post(
            uriTemplate: '/media_items/{id}/copy',
            uriVariables: ['id'],
            controller: MediaItemCopyController::class,
            deserialize: false,
            name: 'media_item_copy',
        ),
        new Patch(uriTemplate: '/media_items/{id}'),
        new Delete(uriTemplate: '/media_items/{id}', processor: MediaItemDeleteProcessor::class),
    ],
    normalizationContext: ['groups' => ['media_item:read']],
    denormalizationContext: ['groups' => ['media_item:write']],
)]
#[ApiFilter(ExistsFilter::class, properties: ['folder'])]
#[ApiFilter(SearchFilter::class, properties: ['folder' => 'exact', 'category' => 'exact', 'type' => 'exact'])]
class MediaItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    #[Groups(['media_item:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mediaItems')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['media_item:write'])]
    private ?MediaFolder $folder = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['media_item:write'])]
    private ?Category $category = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[SerializedName('original_filename')]
    #[Groups(['media_item:read'])]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 127)]
    #[SerializedName('mime_type')]
    #[Groups(['media_item:read'])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 16)]
    #[Groups(['media_item:read'])]
    private ?string $extension = null;

    #[ORM\Column(enumType: MediaItemType::class)]
    #[Groups(['media_item:read'])]
    private ?MediaItemType $type = null;

    #[ORM\Column]
    #[SerializedName('size_bytes')]
    #[Groups(['media_item:read'])]
    private int $sizeBytes = 0;

    #[ORM\Column(length: 512)]
    private ?string $path = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $thumbnailPath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000)]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName('crop_x')]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?int $cropX = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName('crop_y')]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?int $cropY = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName('crop_width')]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?int $cropWidth = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName('crop_height')]
    #[Groups(['media_item:read', 'media_item:write'])]
    private ?int $cropHeight = null;

    #[ORM\Column]
    #[SerializedName('is_hidden_in_api')]
    #[Groups(['media_item:read', 'media_item:write'])]
    private bool $isHiddenInApi = false;

    #[ORM\Column]
    #[SerializedName('created_at')]
    #[Groups(['media_item:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[SerializedName('updated_at')]
    #[Groups(['media_item:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFolder(): ?MediaFolder
    {
        return $this->folder;
    }

    public function setFolder(?MediaFolder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
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

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getSlug(): string
    {
        $name = $this->name ?? '';
        $slug = strtolower($name);
        $slug = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $slug);
        $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : (string) $this->id;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getType(): ?MediaItemType
    {
        return $this->type;
    }

    public function setType(MediaItemType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function setSizeBytes(int $sizeBytes): static
    {
        $this->sizeBytes = $sizeBytes;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function setThumbnailPath(?string $thumbnailPath): static
    {
        $this->thumbnailPath = $thumbnailPath;

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

    public function isHiddenInApi(): bool
    {
        return $this->isHiddenInApi;
    }

    public function setIsHiddenInApi(bool $isHiddenInApi): static
    {
        $this->isHiddenInApi = $isHiddenInApi;

        return $this;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCropX(): ?int
    {
        return $this->cropX;
    }

    public function setCropX(?int $cropX): static
    {
        $this->cropX = $cropX;

        return $this;
    }

    public function getCropY(): ?int
    {
        return $this->cropY;
    }

    public function setCropY(?int $cropY): static
    {
        $this->cropY = $cropY;

        return $this;
    }

    public function getCropWidth(): ?int
    {
        return $this->cropWidth;
    }

    public function setCropWidth(?int $cropWidth): static
    {
        $this->cropWidth = $cropWidth;

        return $this;
    }

    public function getCropHeight(): ?int
    {
        return $this->cropHeight;
    }

    public function setCropHeight(?int $cropHeight): static
    {
        $this->cropHeight = $cropHeight;

        return $this;
    }

    public function hasCropData(): bool
    {
        return $this->cropX !== null
            && $this->cropY !== null
            && $this->cropWidth !== null
            && $this->cropHeight !== null;
    }

    public function isCroppable(): bool
    {
        return \in_array($this->mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
