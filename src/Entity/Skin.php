<?php

namespace App\Entity;

use App\Enum\SkinRarity;
use App\Enum\SkinType;
use App\Repository\SkinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: SkinRepository::class)]
#[Vich\Uploadable]
class Skin
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: \Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du skin est obligatoire")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Vich\UploadableField(mapping: 'skins', fileNameProperty: 'imageUrl')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le prix est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le prix doit être positif ou zéro")]
    private ?int $price = null;

    #[ORM\Column(type: 'string', enumType: SkinRarity::class)]
    #[Assert\NotBlank(message: "La rareté est obligatoire")]
    private ?SkinRarity $rarity = null;

    #[ORM\Column(type: 'string', enumType: SkinType::class, name: 'type')]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    private SkinType $skinType = SkinType::DIGITAL;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiProvider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryMethod = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'skins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getRarity(): ?SkinRarity
    {
        return $this->rarity;
    }

    public function setRarity(SkinRarity $rarity): static
    {
        $this->rarity = $rarity;
        return $this;
    }

    public function getType(): SkinType
    {
        return $this->skinType;
    }

    public function setType(SkinType $skinType): static
    {
        $this->skinType = $skinType;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getApiProvider(): ?string
    {
        return $this->apiProvider;
    }

    public function setApiProvider(?string $apiProvider): static
    {
        $this->apiProvider = $apiProvider;
        return $this;
    }

    public function getDeliveryMethod(): ?string
    {
        return $this->deliveryMethod;
    }

    public function setDeliveryMethod(?string $deliveryMethod): static
    {
        $this->deliveryMethod = $deliveryMethod;
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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;
        return $this;
    }
}
