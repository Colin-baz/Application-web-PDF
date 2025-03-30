<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ProfileController extends AbstractController
{
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscription = $user->getSubscription();
        $fileRepo = $entityManager->getRepository(\App\Entity\File::class);
        $pdfGenerated = $fileRepo->countFilesGeneratedByUser($user->getId());
        $maxPdfLimit = $subscription ? $subscription->getMaxPdf() : 0;

        if ($request->isMethod('POST')) {
            $user->setLastname($request->request->get('lastname'));
            $user->setFirstname($request->request->get('firstname'));
            $user->setEmail($request->request->get('email'));

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'subscription' => $subscription,
            'pdfGenerated' => $pdfGenerated,
            'maxPdfLimit' => $maxPdfLimit
        ]);
    }
    public function deleteAccount(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $fileRepository = $entityManager->getRepository(\App\Entity\File::class);
        $files = $fileRepository->findBy(['user' => $user]);

        foreach ($files as $file) {
            $entityManager->remove($file);
        }

        $entityManager->flush();

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_register');
    }
}
