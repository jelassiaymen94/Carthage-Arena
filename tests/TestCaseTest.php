<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class TestCaseTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(class_exists(\App\Kernel::class));
    }
}
