<?php
// src/Entity/Player.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Player extends User
{
    #[ORM\Column(length: 100)]
    private string $nickname;

    #[ORM\Column(type: "integer")]
    private int $pointsBalance = 0;

    #[ORM\OneToMany(mappedBy: "player", targetEntity: Purchase::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $purchases;

    public function __construct()
    {
        parent::__construct(); // call User constructor
        $this->purchases = new ArrayCollection();
    }

    // === Getters & Setters ===
    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
{
    $this->nickname = $nickname ?? '';
    return $this;
}


    public function getPointsBalance(): int
    {
        return $this->pointsBalance;
    }

    public function setPointsBalance(int $pointsBalance): static
    {
        $this->pointsBalance = $pointsBalance;
        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setPlayer($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            if ($purchase->getPlayer() === $this) {
                $purchase->setPlayer(null);
            }
        }

        return $this;
    }
}
