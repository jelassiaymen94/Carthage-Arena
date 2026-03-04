<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Enum\ReclamationStatus;
use App\Service\ReclamationManager;
use PHPUnit\Framework\TestCase;

class ReclamationManagerTest extends TestCase
{
    public function testValidReclamation()
    {
        $reclamation = new Reclamation();
        $reclamation->setSubject('Titre Valide')
            ->setMessage('Ceci est un message de plus de 15 caractères.')
            ->setAuthor(new User())
            ->setStatus(ReclamationStatus::PENDING);

        $manager = new ReclamationManager();
        $this->assertTrue($manager->validate($reclamation));
    }

    public function testReclamationWithoutSubject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le sujet est obligatoire');

        $reclamation = new Reclamation();
        $reclamation->setMessage('Message de test assez long')
            ->setAuthor(new User());

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }

    public function testReclamationWithMessageTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message doit faire au moins 15 caractères');

        $reclamation = new Reclamation();
        $reclamation->setSubject('Sujet')
            ->setMessage('Trop court')
            ->setAuthor(new User());

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }

    public function testReclamationWithoutAuthor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'auteur est obligatoire");

        $reclamation = new Reclamation();
        $reclamation->setSubject('Sujet')
            ->setMessage('Message de test assez long')
            ->setAuthor(null);

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }

    public function testResolvedReclamationCannotBeModified()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Une réclamation résolue ne peut plus être modifiée');

        $reclamation = new Reclamation();
        $reclamation->setSubject('Sujet')
            ->setMessage('Message de test assez long')
            ->setAuthor(new User())
            ->setStatus(ReclamationStatus::RESOLVED);

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }
}
