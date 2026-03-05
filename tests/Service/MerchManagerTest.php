<?php

namespace App\Tests\Service;

use App\Entity\Merch;
use App\Service\MerchManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MerchManagerTest extends TestCase
{
    public function testValidMerch(): void
    {
        $merch = new Merch();
        $merch->setName('Hoodie');
        $merch->setPrice(2500); 
        $merch->setStock(10);   
        
        $manager = new MerchManager();
        $this->assertTrue($manager->validate($merch));
    }

    public function testMerchWithZeroOrNegativePrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le prix d'un produit doit toujours être supérieur à zéro.");
        
        $merch = new Merch();
        $merch->setName('Hoodie');
        $merch->setStock(10);
        $merch->setPrice(0); 

        $manager = new MerchManager();
        $manager->validate($merch);
    }

    public function testMerchWithNegativeStock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("La quantité en stock ne peut pas être négative.");
        
        $merch = new Merch();
        $merch->setName('Hoodie');
        $merch->setPrice(2500);
        $merch->setStock(-5); 

        $manager = new MerchManager();
        $manager->validate($merch);
    }
}
