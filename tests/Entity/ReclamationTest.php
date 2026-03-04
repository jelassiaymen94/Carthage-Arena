<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Enum\ReclamationCategory;
use App\Enum\ReclamationPriority;
use App\Enum\ReclamationStatus;
use PHPUnit\Framework\TestCase;

class ReclamationTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $reclamation = new Reclamation();
        $author = new User();
        $now = new \DateTimeImmutable();

        $reclamation->setSubject('Problème de connexion')
            ->setMessage('Je ne arrive pas à me connecter à mon compte.')
            ->setCategory(ReclamationCategory::TECHNICAL)
            ->setPriority(ReclamationPriority::HIGH)
            ->setStatus(ReclamationStatus::PENDING)
            ->setAuthor($author)
            ->setCreatedAt($now);

        $this->assertEquals('Problème de connexion', $reclamation->getSubject());
        $this->assertEquals('Je ne arrive pas à me connecter à mon compte.', $reclamation->getMessage());
        $this->assertEquals(ReclamationCategory::TECHNICAL, $reclamation->getCategory());
        $this->assertEquals(ReclamationPriority::HIGH, $reclamation->getPriority());
        $this->assertEquals(ReclamationStatus::PENDING, $reclamation->getStatus());
        $this->assertEquals($author, $reclamation->getAuthor());
        $this->assertEquals($now, $reclamation->getCreatedAt());
    }
}
