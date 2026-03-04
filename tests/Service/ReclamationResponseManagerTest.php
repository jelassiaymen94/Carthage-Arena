<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Entity\ReclamationResponse;
use App\Entity\User;
use App\Enum\ReclamationStatus;
use App\Service\ReclamationResponseManager;
use PHPUnit\Framework\TestCase;

class ReclamationResponseManagerTest extends TestCase
{
    public function testValidResponse()
    {
        $reclamation = new Reclamation();
        $reclamation->setStatus(ReclamationStatus::PENDING);

        $response = new ReclamationResponse();
        $response->setMessage('Ceci est une réponse valide de plus de 10 caractères.')
            ->setAuthor(new User())
            ->setReclamation($reclamation);

        $manager = new ReclamationResponseManager();
        $this->assertTrue($manager->validate($response));
    }

    public function testResponseWithoutMessage()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message de réponse ne peut pas être vide');

        $reclamation = new Reclamation();
        $response = new ReclamationResponse();
        $response->setAuthor(new User())
            ->setReclamation($reclamation);

        $manager = new ReclamationResponseManager();
        $manager->validate($response);
    }

    public function testResponseWithMessageTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message de réponse doit faire au moins 10 caractères');

        $reclamation = new Reclamation();
        $response = new ReclamationResponse();
        $response->setMessage('Court')
            ->setAuthor(new User())
            ->setReclamation($reclamation);

        $manager = new ReclamationResponseManager();
        $manager->validate($response);
    }

    public function testResponseWithoutAuthor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'auteur de la réponse est obligatoire");

        $reclamation = new Reclamation();
        $response = new ReclamationResponse();
        $response->setMessage('Message de réponse valide.')
            ->setReclamation($reclamation);

        $manager = new ReclamationResponseManager();
        $manager->validate($response);
    }

    public function testResponseWithoutReclamation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La réclamation parente est obligatoire');

        $response = new ReclamationResponse();
        $response->setMessage('Message de réponse valide.')
            ->setAuthor(new User());

        $manager = new ReclamationResponseManager();
        $manager->validate($response);
    }

    public function testResponseOnResolvedReclamation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Impossible de répondre à une réclamation déjà résolue');

        $reclamation = new Reclamation();
        $reclamation->setStatus(ReclamationStatus::RESOLVED);

        $response = new ReclamationResponse();
        $response->setMessage('Message de réponse valide.')
            ->setAuthor(new User())
            ->setReclamation($reclamation);

        $manager = new ReclamationResponseManager();
        $manager->validate($response);
    }
}
