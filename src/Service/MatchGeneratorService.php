<?php

namespace App\Service;

use App\Entity\MatchEntity;
use App\Entity\Tournoi;
use App\Enum\MatchStatus;
use App\Enum\TournamentType;

class MatchGeneratorService
{
    /**
     * Generate matches for a tournament based on its type
     * 
     * @param Tournoi $tournoi
     * @return array Array of MatchEntity objects
     * @throws \InvalidArgumentException
     */
    public function generateMatches(Tournoi $tournoi): array
    {
        $this->validateTeamCount($tournoi);

        return match ($tournoi->getType()) {
            TournamentType::ELIMINATION => $this->generateSingleEliminationBracket($tournoi),
            TournamentType::ROUND_ROBIN => $this->generateRoundRobinMatches($tournoi),
            default => throw new \InvalidArgumentException(
                sprintf('Tournament type "%s" is not yet supported for match generation', $tournoi->getType()->value)
            ),
        };
    }

    /**
     * Generate single elimination bracket
     * Requires power of 2 teams (4, 8, 16, 32, etc.)
     */
    private function generateSingleEliminationBracket(Tournoi $tournoi): array
    {
        $teams = $tournoi->getTeams()->toArray();
        $teamCount = count($teams);

        // Validate power of 2
        if ($teamCount < 2 || ($teamCount & ($teamCount - 1)) !== 0) {
            throw new \InvalidArgumentException(
                sprintf('Single elimination requires a power of 2 teams (4, 8, 16, etc.). Current: %d teams', $teamCount)
            );
        }

        // Shuffle teams for fairness
        $teams = $this->shuffleTeams($teams);

        $matches = [];
        $round = 1;
        $currentRoundTeams = $teams;

        // Generate first round matches
        for ($i = 0; $i < count($currentRoundTeams); $i += 2) {
            $match = new MatchEntity();
            $match->setTournoi($tournoi);
            $match->setTeam1($currentRoundTeams[$i]);
            $match->setTeam2($currentRoundTeams[$i + 1]);
            $match->setRound($round);
            $match->setStatus(MatchStatus::SCHEDULED);
            $match->setScheduledAt($tournoi->getDateDebut());

            $matches[] = $match;
        }

        // Generate subsequent rounds (placeholders - winners will be determined later)
        $remainingMatches = count($matches);
        while ($remainingMatches > 1) {
            $round++;
            $remainingMatches = (int) ($remainingMatches / 2);

            for ($i = 0; $i < $remainingMatches; $i++) {
                $match = new MatchEntity();
                $match->setTournoi($tournoi);
                $match->setRound($round);
                $match->setStatus(MatchStatus::SCHEDULED);
                // Teams will be set after previous round completes

                $matches[] = $match;
            }
        }

        return $matches;
    }

    /**
     * Generate round robin matches
     * Every team plays every other team once
     */
    private function generateRoundRobinMatches(Tournoi $tournoi): array
    {
        $teams = $tournoi->getTeams()->toArray();
        $teamCount = count($teams);

        if ($teamCount < 2) {
            throw new \InvalidArgumentException('Round robin requires at least 2 teams');
        }

        // Shuffle teams for fairness
        $teams = $this->shuffleTeams($teams);

        $matches = [];
        $round = 1;

        // Generate all possible pairings
        for ($i = 0; $i < $teamCount; $i++) {
            for ($j = $i + 1; $j < $teamCount; $j++) {
                $match = new MatchEntity();
                $match->setTournoi($tournoi);
                $match->setTeam1($teams[$i]);
                $match->setTeam2($teams[$j]);
                $match->setRound($round);
                $match->setStatus(MatchStatus::SCHEDULED);
                $match->setScheduledAt($tournoi->getDateDebut());

                $matches[] = $match;

                // Distribute matches across rounds for better scheduling
                // Each round should have roughly equal number of matches
                if (count($matches) % (int) ($teamCount / 2) === 0) {
                    $round++;
                }
            }
        }

        return $matches;
    }

    /**
     * Validate tournament has sufficient teams
     */
    private function validateTeamCount(Tournoi $tournoi): void
    {
        $teamCount = $tournoi->getTeams()->count();

        if ($teamCount < 2) {
            throw new \InvalidArgumentException(
                sprintf('Tournament must have at least 2 teams to generate matches. Current: %d teams', $teamCount)
            );
        }
    }

    /**
     * Shuffle teams array for fairness
     */
    private function shuffleTeams(array $teams): array
    {
        shuffle($teams);
        return $teams;
    }
}
