<?php

namespace App\Tests\Service;

use App\Entity\MatchEntity;
use App\Entity\Team;
use App\Service\MatchManager;
use PHPUnit\Framework\TestCase;

class MatchManagerTest extends TestCase
{
    public function testValidMatch()
    {
        $match = new MatchEntity();
        $team1 = new Team();
        $team2 = new Team();

        $match->setTeam1($team1)
            ->setTeam2($team2)
            ->setRound(1)
            ->setScore(['t1' => 10, 't2' => 5]);

        $manager = new MatchManager();
        $this->assertTrue($manager->validate($match));
    }

    public function testMatchWithSameTeams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Les deux équipes d'un match doivent être différentes");

        $team = new Team();
        $match = new MatchEntity();
        $match->setTeam1($team)
            ->setTeam2($team);

        $manager = new MatchManager();
        $manager->validate($match);
    }

    public function testMatchWithNegativeScore()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le score ne peut pas être négatif');

        $match = new MatchEntity();
        $match->setScore(['t1' => -1]);

        $manager = new MatchManager();
        $manager->validate($match);
    }

    public function testMatchWithInvalidRound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le round doit être supérieur à 0');

        $match = new MatchEntity();
        $match->setRound(0);

        $manager = new MatchManager();
        $manager->validate($match);
    }

    public function testCompletedMatchWithoutWinner()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Un match terminé doit avoir un gagnant');

        $match = new MatchEntity();
        $match->setStatus(\App\Enum\MatchStatus::COMPLETED)
            ->setRound(1)
            ->setWinner(null);

        $manager = new MatchManager();
        $manager->validate($match);
    }

    public function testInProgressMatchWithoutTeams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les deux équipes doivent être présentes pour démarrer le match');

        $match = new MatchEntity();
        $match->setStatus(\App\Enum\MatchStatus::IN_PROGRESS)
            ->setRound(1)
            ->setTeam1(new Team())
            ->setTeam2(null);

        $manager = new MatchManager();
        $manager->validate($match);
    }
}
