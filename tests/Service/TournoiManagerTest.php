<?php

namespace App\Tests\Service;

use App\Entity\Tournoi;
use App\Service\TournoiManager;
use PHPUnit\Framework\TestCase;

class TournoiManagerTest extends TestCase
{
    public function testValidTournoi()
    {
        $tournoi = new Tournoi();
        $now = new \DateTimeImmutable();
        $tournoi->setNom('Championnat de Carthage');
        $tournoi->setDateDebut($now);
        $tournoi->setDateFin($now->modify('+1 day'));
        $tournoi->setNbEquipesMax(16);
        $tournoi->setPrizePool(1000);

        $manager = new TournoiManager();
        $this->assertTrue($manager->validate($tournoi));
    }

    public function testTournoiWithoutName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du tournoi est obligatoire');

        $tournoi = new Tournoi();
        $now = new \DateTimeImmutable();
        $tournoi->setDateDebut($now);
        $tournoi->setDateFin($now->modify('+1 day'));

        $manager = new TournoiManager();
        $manager->validate($tournoi);
    }

    public function testTournoiWithInvalidDates()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de fin doit être postérieure à la date de début');

        $tournoi = new Tournoi();
        $now = new \DateTimeImmutable();
        $tournoi->setNom('Test Tournoi');
        $tournoi->setDateDebut($now);
        $tournoi->setDateFin($now->modify('-1 day'));

        $manager = new TournoiManager();
        $manager->validate($tournoi);
    }

    public function testTournoiWithTooFewTeams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le nombre d'équipes max doit être au moins 2");

        $tournoi = new Tournoi();
        $tournoi->setNom('Test')
            ->setDateDebut(new \DateTimeImmutable('tomorrow'))
            ->setDateFin(new \DateTimeImmutable('+2 days'))
            ->setNbEquipesMax(1);

        $manager = new TournoiManager();
        $manager->validate($tournoi);
    }

    public function testTournoiWithNegativePrizePool()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prize pool ne peut pas être négatif');

        $tournoi = new Tournoi();
        $tournoi->setNom('Test')
            ->setDateDebut(new \DateTimeImmutable('tomorrow'))
            ->setDateFin(new \DateTimeImmutable('+2 days'))
            ->setNbEquipesMax(16)
            ->setPrizePool(-100);

        $manager = new TournoiManager();
        $manager->validate($tournoi);
    }

    public function testTournoiWithPastStartDate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de début ne peut pas être dans le passé');

        $tournoi = new Tournoi();
        $tournoi->setNom('Test')
            ->setDateDebut(new \DateTimeImmutable('yesterday'))
            ->setDateFin(new \DateTimeImmutable('tomorrow'))
            ->setNbEquipesMax(16);

        $manager = new TournoiManager();
        $manager->validate($tournoi);
    }
}
