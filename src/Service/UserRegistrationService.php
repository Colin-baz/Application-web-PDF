<?php

namespace App\Service;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\SubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserRegistrationService
{
    private EmailVerifier $emailVerifier;
    private SubscriptionService $subscriptionService;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;
    private FormFactoryInterface $formFactory;

    public function __construct(
        EmailVerifier $emailVerifier,
        SubscriptionService $subscriptionService,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
        FormFactoryInterface $formFactory
    ) {
        $this->emailVerifier = $emailVerifier;
        $this->subscriptionService = $subscriptionService;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
        $this->formFactory = $formFactory;
    }

    public function handleRegistration(Request $request): array
    {
        $user = new User();
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $defaultSubscription = $this->subscriptionService->getDefaultSubscription();
            if ($defaultSubscription) {
                $user->setSubscription($defaultSubscription);
            }

            // Persistance de l'utilisateur
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Envoi de l'email de confirmation
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from('colin.bazelaire@etudiant.univ-reims.fr')
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Connexion automatique de l'utilisateur
            $this->security->login($user);

            // Retourner la réponse avec une redirection vers la page d'accueil après la connexion
            return [
                'success' => true,
                'form' => $form
            ];
        }

        return [
            'success' => false,
            'form' => $form
        ];
    }

    public function handleEmailVerification(Request $request, $user): void
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }
}
