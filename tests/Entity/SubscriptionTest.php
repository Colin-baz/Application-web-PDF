<?php

// tests/Entity/SubscriptionTest.php
namespace App\Tests\Entity;

use App\Entity\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testGetterAndSetter()
    {
        // Création d'une instance de l'entité Subscription
        $subscription = new Subscription();

        // Définition de données de test
        $name = 'Basic Plan';
        $subscription->setName($name);

        // Vérification des getters
        $this->assertEquals($name, $subscription->getName());
    }
}
