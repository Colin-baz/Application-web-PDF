<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $subscription = new Subscription();

        $subscription->setName('Entreprise');
        $this->assertEquals('Entreprise', $subscription->getName());

        $subscription->setDescription('Nombre de PDF limité à 10000');
        $this->assertEquals('Nombre de PDF limité à 10000', $subscription->getDescription());

        $subscription->setMaxPdf(10000);
        $this->assertEquals(10000, $subscription->getMaxPdf());

        $subscription->setPrice(99);
        $this->assertEquals(99, $subscription->getPrice());

        $subscription->setSpecialPrice(79);
        $this->assertEquals(79, $subscription->getSpecialPrice());

        $specialPriceFrom = new \DateTime('2025-08-08');
        $subscription->setSpecialPriceFrom($specialPriceFrom);
        $this->assertEquals($specialPriceFrom, $subscription->getSpecialPriceFrom());

        $specialPriceTo = new \DateTime('2025-12-31');
        $subscription->setSpecialPriceTo($specialPriceTo);
        $this->assertEquals($specialPriceTo, $subscription->getSpecialPriceTo());
    }

    public function testUsersCollection()
    {
        $subscription = new Subscription();
        $user = $this->createMock(User::class);

        $this->assertCount(0, $subscription->getUsers());

        $subscription->addUser($user);
        $this->assertCount(1, $subscription->getUsers());
        $this->assertTrue($subscription->getUsers()->contains($user));

        $subscription->removeUser($user);
        $this->assertCount(0, $subscription->getUsers());
    }
}
