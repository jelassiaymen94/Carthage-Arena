<?php

namespace App\Tests\Entity;

use App\Entity\Game;
use App\Entity\MatchEntity;
use App\Entity\Team;
use App\Entity\Tournoi;
use App\Entity\User;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TournoiTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }
    public function testGettersAndSetters(): void
    {
        $tournoi = new Tournoi();
        $now = new \DateTimeImmutable();
        $tomorrow = $now->modify('+1 day');
        $game = new Game();
        $referee = new User();
        $winner = new Team();

        $tournoi->setNom('Championnat de Carthage')
            ->setDateDebut($now)
            ->setDateFin($tomorrow)
            ->setNbEquipesMax(16)
            ->setPrizePool(1000)
            ->setStatus(TournamentStatus::ONGOING)
            ->setType(TournamentType::ELIMINATION)
            ->setGame($game)
            ->setReferee($referee)
            ->setWinner($winner)
            ->setPlace('Tunis');

        $this->assertEquals('Championnat de Carthage', $tournoi->getNom());
        $this->assertEquals($now, $tournoi->getDateDebut());
        $this->assertEquals($tomorrow, $tournoi->getDateFin());
        $this->assertEquals(16, $tournoi->getNbEquipesMax());
        $this->assertEquals(1000, $tournoi->getPrizePool());
        $this->assertEquals(TournamentStatus::ONGOING, $tournoi->getStatus());
        $this->assertEquals(TournamentType::ELIMINATION, $tournoi->getType());
        $this->assertEquals($game, $tournoi->getGame());
        $this->assertEquals($referee, $tournoi->getReferee());
        $this->assertEquals($winner, $tournoi->getWinner());
        $this->assertEquals('Tunis', $tournoi->getPlace());
    }

    public function testValidation(): void
    {
        $tournoi = new Tournoi();

        // Test Nom Blank
        $tournoi->setNom('');
        $errors = $this->validator->validate($tournoi);
        $this->assertGreaterThan(0, count($errors));

        // Test Valid Tournoi
        $now = new \DateTimeImmutable();
        $tournoi->setNom('Valid Name')
            ->setDateDebut($now)
            ->setDateFin($now->modify('+1 day'))
            ->setNbEquipesMax(16)
            ->setType(TournamentType::ELIMINATION)
            ->setPrizePool(100);

        $errors = $this->validator->validate($tournoi);
        $this->assertCount(0, $errors);

        // Test DateFin before DateDebut
        $tournoi->setDateFin($now->modify('-1 day'));
        $errors = $this->validator->validate($tournoi);
        $this->assertGreaterThan(0, count($errors));

        // Test Negative PrizePool
        $tournoi->setDateFin($now->modify('+1 day'))
            ->setPrizePool(-100);
        $errors = $this->validator->validate($tournoi);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testAddRemoveTeam(): void
    {
        $tournoi = new Tournoi();
        $team = new Team();

        $this->assertCount(0, $tournoi->getTeams());

        $tournoi->addTeam($team);
        $this->assertCount(1, $tournoi->getTeams());
        $this->assertTrue($tournoi->getTeams()->contains($team));

        $tournoi->removeTeam($team);
        $this->assertCount(0, $tournoi->getTeams());
        $this->assertFalse($tournoi->getTeams()->contains($team));
    }

    public function testAddRemoveMatch(): void
    {
        $tournoi = new Tournoi();
        $match = new MatchEntity();

        $this->assertCount(0, $tournoi->getMatches());

        $tournoi->addMatch($match);
        $this->assertCount(1, $tournoi->getMatches());
        $this->assertTrue($tournoi->getMatches()->contains($match));
        $this->assertEquals($tournoi, $match->getTournoi());

        $tournoi->removeMatch($match);
        $this->assertCount(0, $tournoi->getMatches());
        $this->assertFalse($tournoi->getMatches()->contains($match));
        $this->assertNull($match->getTournoi());
    }
}
