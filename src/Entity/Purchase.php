<?php
// src/Entity/Purchase.php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $purchaseDate;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "integer")]
    private int $totalAmount;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: "purchases")]
   #[ORM\JoinColumn(nullable: true)] // allow null for removal
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    // === Constructor ===
    public function __construct(?Player $player = null, ?Product $product = null, int $quantity = 0)
{
    $this->player = $player;
    $this->product = $product;
    $this->quantity = $quantity;
    $this->purchaseDate = new \DateTime();

    if ($product !== null && $quantity > 0) {
        $this->totalAmount = $product->getPricePoints() * $quantity;
    } else {
        $this->totalAmount = 0;
    }
}


    // === Getters ===
    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getPurchaseDate(): \DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    // === Setters ===
    public function setPurchaseDate(\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        // Recalculate totalAmount if needed
        $this->totalAmount = $this->product->getPricePoints() * $quantity;
        return $this;
    }
// === Setter for totalAmount ===
public function setTotalAmount(int $totalAmount): self
{
    $this->totalAmount = $totalAmount;
    return $this;
}

   

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        // Recalculate totalAmount if quantity is already set
        $this->totalAmount = $product->getPricePoints() * $this->quantity;
        return $this;
    }
    public function setPlayer(?Player $player): self
{
    $this->player = $player;
    return $this;
}
}
