<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Merch $merch = null;
    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $totalPrice = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $purchaseDate = null;

    public function __construct()
    {
        $this->purchaseDate = new DateTimeImmutable();
        $this->quantity = 1;
        $this->totalPrice = 0;
    }

    // Getters & Setters

    public function getId(): ?Uuid { return $this->id; }

    public function getMerch(): ?Merch { return $this->merch; }
    public function setMerch(?Merch $merch): static {
        $this->merch = $merch;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static {
        $this->user = $user;
        return $this;
    }

    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static {
        $this->quantity = $quantity;
        return $this;
    }

    public function getTotalPrice(): ?int { return $this->totalPrice; }
    public function setTotalPrice(int $totalPrice): static {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable {
        return $this->purchaseDate;
    }
    public function calculateTotalPrice(): static
{
    if ($this->merch && $this->quantity !== null) {
        $this->totalPrice = $this->merch->getPrice() * $this->quantity;
    }
    return $this;
}
}