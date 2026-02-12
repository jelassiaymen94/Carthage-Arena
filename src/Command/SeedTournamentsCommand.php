<?php

namespace App\Command;

use App\Entity\Game;
use App\Entity\Tournoi;
use App\Entity\Team;
use App\Enum\GameStatus;
use App\Enum\GameType;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-tournaments',
    description: 'Seeds the database with 3 tournaments and assigns existing teams',
)]
class SeedTournamentsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. Ensure Games exist or create them
        $gameRepository = $this->entityManager->getRepository(Game::class);
        $games = $gameRepository->findAll();

        if (empty($games)) {
            $io->note('No games found. Creating sample games...');

            $game1 = new Game();
            $game1->setName('League of Legends');
            $game1->setType(GameType::MOBA);
            $game1->setStatus(GameStatus::ACTIVE);
            $game1->setImageUrl('https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&h=400&fit=crop');
            $this->entityManager->persist($game1);

            $game2 = new Game();
            $game2->setName('Valorant');
            $game2->setType(GameType::FPS);
            $game2->setStatus(GameStatus::ACTIVE);
            $game2->setImageUrl('https://images.unsplash.com/photo-1624138784181-dc7f5b75e52e?w=800&h=400&fit=crop');
            $this->entityManager->persist($game2);

            $this->entityManager->flush();
            $games = [$game1, $game2];
            $io->note('Created 2 games.');
        } else {
            $io->note('Found ' . count($games) . ' existing games.');
        }

        // 2. Fetch existing Teams
        $teamRepository = $this->entityManager->getRepository(Team::class);
        $teams = $teamRepository->findAll();

        if (empty($teams)) {
            $io->error('No teams found. Please run app:seed-data first.');
            return Command::FAILURE;
        }

        $io->note('Found ' . count($teams) . ' teams.');

        // 3. Create 3 Tournaments
        $tournamentsData = [
            [
                'name' => 'Carthage Winter Cup',
                'game' => $games[0], // LoL
                'status' => TournamentStatus::UPCOMING,
                'dateDebut' => new \DateTimeImmutable('+1 week'),
                'dateFin' => new \DateTimeImmutable('+2 weeks'),
                'teams' => array_slice($teams, 0, 2) // Join first 2 teams
            ],
            [
                'name' => 'Valorant Spike Rush',
                'game' => $games[1] ?? $games[0], // Valorant or fallback
                'status' => TournamentStatus::ONGOING,
                'dateDebut' => new \DateTimeImmutable('-1 day'),
                'dateFin' => new \DateTimeImmutable('+5 days'),
                'teams' => array_slice($teams, 0, 4) // Join all 4 teams
            ],
            [
                'name' => 'Legends Showdown',
                'game' => $games[0], // LoL
                'status' => TournamentStatus::UPCOMING,
                'dateDebut' => new \DateTimeImmutable('+1 month'),
                'dateFin' => new \DateTimeImmutable('+1 month 1 week'),
                'teams' => [$teams[0] ?? null] // Join 1 team
            ]
        ];

        foreach ($tournamentsData as $data) {
            $tournoi = new Tournoi();
            $tournoi->setNom($data['name']);
            $tournoi->setGame($data['game']);
            $tournoi->setType(TournamentType::ELIMINATION);
            $tournoi->setStatus($data['status']);
            $tournoi->setDateDebut($data['dateDebut']);
            $tournoi->setDateFin($data['dateFin']);
            $tournoi->setNbEquipesMax(16);
            $tournoi->setPrizePool(5000);

            // Add teams
            foreach ($data['teams'] as $team) {
                if ($team) {
                    $tournoi->addTeam($team);
                }
            }

            $this->entityManager->persist($tournoi);
        }

        $this->entityManager->flush();

        $io->success('Created 3 tournaments and assigned teams successfully!');

        return Command::SUCCESS;
    }
}
