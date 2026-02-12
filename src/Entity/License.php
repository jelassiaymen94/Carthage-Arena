<?php

namespace App\Entity;

use App\Repository\LicenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LicenseRepository::class)]
#[ORM\Table(name: 'license')]
#[ORM\UniqueConstraint(name: 'UNIQ_LICENSE_CODE', fields: ['licenseCode'])]
class License
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le code de licence est obligatoire.')]
    private ?string $licenseCode = null;

    #[ORM\Column]
    private bool $isUsed = false;

    #[ORM\OneToOne(inversedBy: 'license', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assignedTo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isUsed = false;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getLicenseCode(): ?string
    {
        return $this->licenseCode;
    }

    public function setLicenseCode(string $licenseCode): static
    {
        $this->licenseCode = $licenseCode;
        return $this;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): static
    {
        $this->isUsed = $isUsed;
        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;
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

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeImmutable $usedAt): static
    {
        $this->usedAt = $usedAt;
        return $this;
    }

    /**
     * Mark this license as used and assign it to a user
     */
    public function assignToUser(User $user): static
    {
        $this->isUsed = true;
        $this->assignedTo = $user;
        $this->usedAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * Check if this license is available for use
     */
    public function isAvailable(): bool
    {
        return !$this->isUsed && $this->assignedTo === null;
    }
}
