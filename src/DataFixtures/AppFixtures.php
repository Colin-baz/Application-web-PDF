<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $subscriptions = [
            [
                'name' => 'Gratuit',
                'description' => 'Limité à 10 générés par jour',
                'maxPdf' => 10,
                'price' => 0,
                'specialPrice' => 0,
                'specialPriceFrom' => new \DateTime(),
                'specialPriceTo' => new \DateTime()
            ],
            [
                'name' => 'Pro',
                'description' => 'Limité à 100 générés par jour',
                'maxPdf' => 100,
                'price' => 9.99,
                'specialPrice' => 7.99,
                'specialPriceFrom' => new \DateTime('2025-08-08'),
                'specialPriceTo' => new \DateTime('2025-06-30')
            ],
            [
                'name' => 'Entreprise',
                'description' => 'Limité à 10000 générés par jour',
                'maxPdf' => 10000,
                'price' => 99.00,
                'specialPrice' => 79.00,
                'specialPriceFrom' => new \DateTime('2025-08-08'),
                'specialPriceTo' => new \DateTime('2025-12-31')
            ]
        ];

        foreach ($subscriptions as $subscriptionData) {
            $subscription = new Subscription();
            $subscription->setName($subscriptionData['name']);
            $subscription->setDescription($subscriptionData['description']);
            $subscription->setMaxPdf($subscriptionData['maxPdf']);
            $subscription->setPrice($subscriptionData['price']);
            $subscription->setSpecialPrice($subscriptionData['specialPrice']);
            $subscription->setSpecialPriceFrom($subscriptionData['specialPriceFrom']);
            $subscription->setSpecialPriceTo($subscriptionData['specialPriceTo']);

            $manager->persist($subscription);
        }

        $manager->flush();
    }
}


