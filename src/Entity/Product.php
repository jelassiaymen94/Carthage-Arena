<?php
// src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "product_type", type: "string")]
#[ORM\DiscriminatorMap([
    "skin" => Skin::class,
    "merch" => Merch::class
])]
abstract class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    protected string $name;

    #[ORM\Column(type: "text")]
    protected string $description;

    #[ORM\Column(type: "integer")]
    protected int $pricePoints;

    #[ORM\Column(type: "boolean")]
    protected bool $available = true;

    // === Constructeur optionnel ===
   // === Constructeur optionnel ===
/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
public function __construct(string $name, string $description, int $pricePoints)
{
    $this->name = $name;
    $this->description = $description;
    $this->pricePoints = $pricePoints;
    $this->available = true; 
}



    // === Getters ===
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPricePoints(): int
    {
        return $this->pricePoints;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    // === Setters ===
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setPricePoints(int $pricePoints): self
    {
        $this->pricePoints = $pricePoints;
        return $this;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;
        return $this;
    }
}
