<?php

namespace App\Controller;

use App\Service\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private UserRegistrationService $registrationService;

    public function __construct(UserRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $result = $this->registrationService->handleRegistration($request);

        if ($result['success']) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $result['form']->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        try {
            $this->registrationService->handleEmailVerification($request, $this->getUser());
            $this->addFlash('success', 'Your email address has been verified.');
        } catch (\Exception $exception) {
            $this->addFlash('verify_email_error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_register');
    }
}
