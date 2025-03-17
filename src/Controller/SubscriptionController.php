<?php

// src/Controller/SubscriptionController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractController
{
    public function changeSubscription(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscriptionRepo = $entityManager->getRepository(Subscription::class);
        $subscriptions = $subscriptionRepo->findAll();

        $subscription = $user->getSubscription();

        if ($request->isMethod('POST')) {
            $selectedPlan = $request->get('subscription');
            $selectedSubscription = null;

            foreach ($subscriptions as $subscription) {
                if ($subscription->getName() === $selectedPlan) {
                    $selectedSubscription = $subscription;
                    break;
                }
            }

            if ($selectedSubscription) {
                $user->setSubscription($selectedSubscription);
                $entityManager->flush();

                return $this->redirectToRoute('subscription_index');
            }
        }

        // Afficher la page avec les abonnements
        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
        ]);
    }
}



