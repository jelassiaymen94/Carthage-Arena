<?php

namespace App\Entity;

use App\Enum\AccountStatus;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà pris.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
        maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères.'
    )]
    private ?string $username = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: 'Le pseudo ne peut pas dépasser 50 caractères.')]
    private ?string $nickname = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['registration'])]
    #[Assert\Length(
        min: 6,
        minMessage: 'Le mot de passe doit contenir au moins 6 caractères.',
        groups: ['registration']
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255, enumType: AccountStatus::class)]
    private AccountStatus $status = AccountStatus::ACTIVE;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'integer')]
    private int $balance = 0;

    #[ORM\OneToOne(mappedBy: 'assignedTo', cascade: ['persist'])]
    private ?License $license = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?AuthToken $authToken = null;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: TeamMembership::class, orphanRemoval: true)]
    private Collection $teamMemberships;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = AccountStatus::ACTIVE;
        $this->teamMemberships = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary, sensitive data
    }

    public function getStatus(): AccountStatus
    {
        return $this->status;
    }

    public function setStatus(AccountStatus $status): static
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

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): static
    {
        $this->balance = $balance;
        return $this;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }

    public function setLicense(?License $license): static
    {
        // Set the owning side of the relation if necessary
        if ($license !== null && $license->getAssignedTo() !== $this) {
            $license->setAssignedTo($this);
        }

        $this->license = $license;
        return $this;
    }

    /**
     * Helper method to get license code as string
     */
    public function getLicenseCode(): ?string
    {
        return $this->license?->getLicenseCode();
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getAuthToken(): ?AuthToken
    {
        return $this->authToken;
    }

    public function setAuthToken(?AuthToken $authToken): static
    {
        if ($authToken !== null && $authToken->getUser() !== $this) {
            $authToken->setUser($this);
        }

        $this->authToken = $authToken;

        return $this;
    }

    /**
     * @return Collection<int, TeamMembership>
     */
    public function getTeamMemberships(): Collection
    {
        return $this->teamMemberships;
    }

    public function addTeamMembership(TeamMembership $teamMembership): static
    {
        if (!$this->teamMemberships->contains($teamMembership)) {
            $this->teamMemberships->add($teamMembership);
            $teamMembership->setPlayer($this);
        }

        return $this;
    }

    public function removeTeamMembership(TeamMembership $teamMembership): static
    {
        if ($this->teamMemberships->removeElement($teamMembership)) {
            // set the owning side to null (unless already changed)
            if ($teamMembership->getPlayer() === $this) {
                $teamMembership->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * Helper to get avatar from profile
     */
    public function getAvatar(): ?string
    {
        return $this->profile?->getAvatarUrl();
    }

    /**
     * Helper to set avatar on profile
     */
    public function setAvatar(?string $avatar): static
    {
        if ($this->profile) {
            $this->profile->setAvatarUrl($avatar);
        }
        return $this;
    }

    /**
     * Used by templates that reference user.name
     */
    public function getName(): string
    {
        return $this->nickname ?? $this->username ?? '';
    }
}
