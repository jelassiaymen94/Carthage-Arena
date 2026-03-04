<?php

namespace App\Tests\Entity;

use App\Entity\MatchEntity;
use App\Entity\Team;
use App\Entity\Tournoi;
use App\Enum\MatchStatus;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $match = new MatchEntity();
        $now = new \DateTimeImmutable();
        $tournoi = new Tournoi();
        $team1 = new Team();
        $team2 = new Team();
        $winner = new Team();
        $score = ['team1' => 2, 'team2' => 1];

        $match->setRound(1)
            ->setStatus(MatchStatus::IN_PROGRESS)
            ->setScheduledAt($now)
            ->setStartedAt($now)
            ->setCompletedAt($now->modify('+1 hour'))
            ->setScore($score)
            ->setTournoi($tournoi)
            ->setTeam1($team1)
            ->setTeam2($team2)
            ->setWinner($winner);

        $this->assertEquals(1, $match->getRound());
        $this->assertEquals(MatchStatus::IN_PROGRESS, $match->getStatus());
        $this->assertEquals($now, $match->getScheduledAt());
        $this->assertEquals($now, $match->getStartedAt());
        $this->assertNotNull($match->getCompletedAt());
        $this->assertEquals($score, $match->getScore());
        $this->assertEquals($tournoi, $match->getTournoi());
        $this->assertEquals($team1, $match->getTeam1());
        $this->assertEquals($team2, $match->getTeam2());
        $this->assertEquals($winner, $match->getWinner());
    }
}
