<?php

namespace App\Entity;

use App\Enum\ReclamationCategory;
use App\Enum\ReclamationPriority;
use App\Enum\ReclamationStatus;
use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Reclamation
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le sujet est obligatoire")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Le sujet doit faire au moins 5 caractères")]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le message est obligatoire")]
    #[Assert\Length(min: 10, minMessage: "Le message doit faire au moins 10 caractères")]
    private ?string $message = null;

    #[ORM\Column(enumType: ReclamationCategory::class)]
    #[Assert\NotNull(message: "La catégorie est obligatoire")]
    private ?ReclamationCategory $category = null;

    #[ORM\Column(enumType: ReclamationPriority::class)]
    private ?ReclamationPriority $priority = null;

    #[ORM\Column(enumType: ReclamationStatus::class)]
    private ?ReclamationStatus $status = ReclamationStatus::PENDING;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: ReclamationResponse::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $responses;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = ReclamationStatus::PENDING;
        $this->priority = ReclamationPriority::MEDIUM; // Default priority
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getCategory(): ?ReclamationCategory
    {
        return $this->category;
    }

    public function setCategory(ReclamationCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPriority(): ?ReclamationPriority
    {
        return $this->priority;
    }

    public function setPriority(ReclamationPriority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?ReclamationStatus
    {
        return $this->status;
    }

    public function setStatus(ReclamationStatus $status): static
    {
        $this->status = $status;

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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, ReclamationResponse>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(ReclamationResponse $response): static
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setReclamation($this);
        }

        return $this;
    }

    public function removeResponse(ReclamationResponse $response): static
    {
        if ($this->responses->removeElement($response)) {
            // set the owning side to null (unless already changed)
            if ($response->getReclamation() === $this) {
                $response->setReclamation(null);
            }
        }

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
