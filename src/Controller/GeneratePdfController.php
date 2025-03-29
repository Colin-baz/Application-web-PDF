<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class GeneratePdfController extends AbstractController
{
    private GotenbergService $pdfService;
    private EntityManagerInterface $entityManager;

    public function __construct(GotenbergService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    public function generatePdf(Request $request): Response
    {
        $user = $this->getUser();
        $subscription = $user->getSubscription();
        $maxPdfLimit = $subscription ? $subscription->getMaxPdf() : 0;

        $pdfGenerated = $this->entityManager->getRepository(File::class)
            ->countFilesGeneratedByUser($user->getId());

        $form = $this->createFormBuilder()
            ->add('url', null, ['required' => true])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($pdfGenerated >= $maxPdfLimit) {
                $this->addFlash('error', "Vous avez atteint votre limite de génération de PDFs (Max: $maxPdfLimit)");
                return $this->render('generate_pdf/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $url = $form->getData()['url'];
            $pdfContent = $this->pdfService->generatePdfFromUrl($url);

            $pdfFileName = 'generated_pdf_' . uniqid() . '.pdf';
            $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/pdf/' . $pdfFileName;

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
        }

        return $this->render('generate_pdf/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
