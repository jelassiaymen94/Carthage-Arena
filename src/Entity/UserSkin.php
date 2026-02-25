<?php

namespace App\Entity;

use App\Repository\UserSkinRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserSkinRepository::class)]
#[ORM\Table(name: 'user_skin')]
class UserSkin
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Skin::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skin $skin = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $purchasedAt = null;

    #[ORM\Column(length: 255, options: ['default' => 'active'])]
    private string $status = 'active';

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->purchasedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSkin(): ?Skin
    {
        return $this->skin;
    }

    public function setSkin(?Skin $skin): static
    {
        $this->skin = $skin;
        return $this;
    }

    public function getPurchasedAt(): ?\DateTimeImmutable
    {
        return $this->purchasedAt;
    }

    public function setPurchasedAt(\DateTimeImmutable $purchasedAt): static
    {
        $this->purchasedAt = $purchasedAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
