<?php
// src/Entity/Skin.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Skin extends Product
{
    #[ORM\Column(type: "string", length: 100)]
    private string $rarity;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    // === Constructeur optionnel ===
    public function __construct()
    {
        // Optionally, set defaults for Product properties
        $this->available = true;
    }

    // === Getters ===
    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    // === Setters ===
    public function setRarity(string $rarity): self
    {
        $this->rarity = $rarity;
        return $this;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
