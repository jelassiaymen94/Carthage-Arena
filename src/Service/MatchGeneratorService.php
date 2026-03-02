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

    private function generateSingleEliminationBracket(Tournoi $tournoi): array
    {
        $teams = $tournoi->getTeams()->toArray();
        $teamCount = count($teams);

        if ($teamCount < 2) {
            throw new \InvalidArgumentException('Single elimination requires at least 2 teams');
        }

        // Shuffle teams for fairness
        $teams = $this->shuffleTeams($teams);

        $matches = [];
        $round = 1;

        // Find the largest power of 2 less than or equal to teamCount
        $pow2 = 1;
        while ($pow2 * 2 < $teamCount) {
            $pow2 *= 2;
        }

        // Number of matches in Round 1 (Qualifiers to get to a power of 2 for Round 2)
        // If teamCount is 8, pow2 is 4. M = 8 - 4 = 4. (Standard 8-team bracket)
        // If teamCount is 5, pow2 is 4. M = 5 - 4 = 1. (1 match, 3 byes)
        $round1MatchesCount = $teamCount - $pow2;

        for ($i = 0; $i < $round1MatchesCount; $i++) {
            $match = new MatchEntity();
            $match->setTournoi($tournoi);
            $match->setTeam1($teams[$i * 2]);
            $match->setTeam2($teams[$i * 2 + 1]);
            $match->setRound($round);
            $match->setStatus(MatchStatus::SCHEDULED);
            $match->setScheduledAt($tournoi->getDateDebut());
            $matches[] = $match;
        }

        // Total matches in a single elimination bracket is always N-1
        $totalMatchesNeeded = $teamCount - 1;
        $createdMatchesCount = count($matches);

        // Subsequent rounds
        while ($createdMatchesCount < $totalMatchesNeeded) {
            $round++;
            // The number of participants in this round will be half the previous "virtual" participants
            // This is complex, but we know we just need to fill up to N-1 matches.
            // For simplicity, we can just create the remaining matches as placeholders.

            // Remaining matches for this round is pow2 / 2, then pow2 / 4 etc.
            $matchesInThisRound = $pow2 / 2;
            if ($matchesInThisRound < 1)
                $matchesInThisRound = 1;

            for ($i = 0; $i < $matchesInThisRound; $i++) {
                if ($createdMatchesCount >= $totalMatchesNeeded)
                    break;

                $match = new MatchEntity();
                $match->setTournoi($tournoi);
                $match->setRound($round);
                $match->setStatus(MatchStatus::SCHEDULED);
                // For teams with byes in Round 1, we can pre-set them in Round 2 if it's the right round
                // But usually, it's better to let the management logic handle advancements.

                $matches[] = $match;
                $createdMatchesCount++;
            }
            $pow2 /= 2;
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
