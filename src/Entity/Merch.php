<?php
// src/Entity/Merch.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Merch extends Product
{
    #[ORM\Column(type: "integer")]
    private int $stock;

    #[ORM\Column(type: "string", length: 50)]
    private string $size;
   
    // === Constructeur optionnel ===
    public function __construct()
    {
        // Optionally, set defaults for Product properties
        $this->available = true;
    }

    // === Getters ===
    public function getStock(): int
    {
        return $this->stock;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    // === Setters ===
    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }
}
