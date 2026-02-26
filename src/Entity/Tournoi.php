<?php

namespace App\Entity;

use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use App\Repository\TournoiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\TournoiMatchGenerationController;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TournoiRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['tournoi:read']]),
        new GetCollection(normalizationContext: ['groups' => ['tournoi:read']]),
        new Post(denormalizationContext: ['groups' => ['tournoi:write']]),
        new Post(
            uriTemplate: '/tournois/{id}/generate',
            controller: TournoiMatchGenerationController::class,
            name: 'generate_matches',
        )
    ],
    normalizationContext: ['groups' => ['tournoi:read']],
    denormalizationContext: ['groups' => ['tournoi:write']],
)]
class Tournoi
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['tournoi:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['tournoi:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank(message: "Le nom du tournoi est obligatoire")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank(message: "Le nombre d'équipes max est obligatoire")]
    #[Assert\Positive(message: "Le nombre d'équipes doit être positif")]
    private ?int $nbEquipesMax = null;

    #[ORM\Column]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $prizePool = 0;

    #[ORM\Column(type: 'string', enumType: TournamentStatus::class)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private TournamentStatus $status = TournamentStatus::UPCOMING;

    #[ORM\Column(type: 'string', enumType: TournamentType::class)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    #[Assert\NotBlank(message: "Le type de tournoi est obligatoire")]
    private ?TournamentType $type = null;

    #[ORM\ManyToOne(inversedBy: 'tournois')]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private ?Game $game = null;

    #[ORM\ManyToMany(targetEntity: Team::class)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'tournoi', targetEntity: MatchEntity::class)]
    #[Groups(['tournoi:read'])]
    private Collection $matches;

    #[ORM\ManyToOne]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private ?Team $winner = null;

    #[ORM\ManyToOne]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private ?User $referee = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tournoi:read', 'tournoi:write'])]
    private ?string $place = null;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getNbEquipesMax(): ?int
    {
        return $this->nbEquipesMax;
    }

    public function setNbEquipesMax(int $nbEquipesMax): static
    {
        $this->nbEquipesMax = $nbEquipesMax;

        return $this;
    }

    public function getPrizePool(): ?int
    {
        return $this->prizePool;
    }

    public function setPrizePool(int $prizePool): static
    {
        $this->prizePool = $prizePool;

        return $this;
    }

    public function getStatus(): TournamentStatus
    {
        return $this->status;
    }

    public function setStatus(TournamentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?TournamentType
    {
        return $this->type;
    }

    public function setType(TournamentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }


    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        $this->teams->removeElement($team);

        return $this;
    }

    /**
     * @return Collection<int, MatchEntity>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(MatchEntity $match): static
    {
        if (!$this->matches->contains($match)) {
            $this->matches->add($match);
            $match->setTournoi($this);
        }

        return $this;
    }

    public function removeMatch(MatchEntity $match): static
    {
        if ($this->matches->removeElement($match)) {
            if ($match->getTournoi() === $this) {
                $match->setTournoi(null);
            }
        }

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getReferee(): ?User
    {
        return $this->referee;
    }

    public function setReferee(?User $referee): static
    {
        $this->referee = $referee;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): static
    {
        $this->place = $place;

        return $this;
    }
}
