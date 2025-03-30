<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\CustomPdfType;
use DateTime;

class CustomPdfController extends AbstractController
{
    private GotenbergService $pdfService;
    private EntityManagerInterface $entityManager;

    public function __construct(GotenbergService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    public function generatePdfFromWysiwyg(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscription = $user->getSubscription();
        $maxPdfLimit = $subscription ? $subscription->getMaxPdf() : 0;
        $pdfGenerated = $this->entityManager->getRepository(File::class)
            ->countFilesGeneratedByUser($user->getId());

        $form = $this->createForm(CustomPdfType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($pdfGenerated >= $maxPdfLimit) {
                $this->addFlash('error', "Vous avez atteint votre limite de génération de PDFs (Max: $maxPdfLimit)");
                return $this->render('generate_pdf/wysiwyg_form.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $htmlContent = $form->get('content')->getData();

            try {
                $pdfContent = $this->pdfService->generateCustomPdf($htmlContent);

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/pdf/';
                $pdfFileName = 'custom_pdf_' . uniqid() . '.pdf';
                $pdfFilePath = $uploadDir . $pdfFileName;

                file_put_contents($pdfFilePath, $pdfContent);

                $file = new File();
                $file->setUser($user)
                    ->setName($pdfFileName)
                    ->setCreatedAt(new DateTime());

                $this->entityManager->persist($file);
                $this->entityManager->flush();

                return $this->render('generate_pdf/show_pdf.html.twig', [
                    'pdfFilePath' => '/pdf/' . $pdfFileName,
                ]);
            } catch (\Exception $e) {
                return new Response("Erreur lors de la génération du PDF : " . $e->getMessage(), 500);
            }
        }

        return $this->render('generate_pdf/wysiwyg_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
