<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\ReclamationResponse;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReclamationResponseTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $response = new ReclamationResponse();
        $reclamation = new Reclamation();
        $author = new User();
        $now = new \DateTimeImmutable();

        $response->setMessage('Ceci est une réponse de test.')
            ->setCreatedAt($now)
            ->setIsAdminResponse(true)
            ->setReclamation($reclamation)
            ->setAuthor($author);

        $this->assertEquals('Ceci est une réponse de test.', $response->getMessage());
        $this->assertEquals($now, $response->getCreatedAt());
        $this->assertTrue($response->isIsAdminResponse());
        $this->assertEquals($reclamation, $response->getReclamation());
        $this->assertEquals($author, $response->getAuthor());
    }
}
