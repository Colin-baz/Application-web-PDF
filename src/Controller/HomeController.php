<?php

// src/Controller/HomeController.php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class HomeController extends AbstractController
{
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscription = $user->getSubscription();

        $today = new DateTime('today 00:00:00');
        $tomorrow = new DateTime('tomorrow 00:00:00');

        $pdfGeneratedToday = $entityManager->getRepository(File::class)
            ->countPdfGeneratedByUserOnDate($user->getId(), $today, $tomorrow);

        $maxPdfLimit = $subscription ? $subscription->getMaxPdf() : 0;

        $progress = $maxPdfLimit > 0 ? ($pdfGeneratedToday / $maxPdfLimit) * 100 : 0;

        return $this->render('home/index.html.twig', [
            'progress' => $progress,
            'pdfGeneratedToday' => $pdfGeneratedToday,
            'maxPdfLimit' => $maxPdfLimit
        ]);
    }
}
