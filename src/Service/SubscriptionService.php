<?php

namespace App\Service;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getDefaultSubscription(): ?Subscription
    {
        return $this->entityManager->getRepository(Subscription::class)->findOneBy(['name' => 'Gratuit']);
    }
}
