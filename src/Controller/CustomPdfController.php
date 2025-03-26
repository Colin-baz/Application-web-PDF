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
        $form = $this->createForm(CustomPdfType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $htmlContent = $form->get('content')->getData();

            try {
                $pdfContent = $this->pdfService->generateCustomPdf($htmlContent);

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/pdf/';

                $pdfFileName = 'custom_pdf_' . uniqid() . '.pdf';
                $pdfFilePath = $uploadDir . $pdfFileName;

                file_put_contents($pdfFilePath, $pdfContent);

                $file = new File();
                $file->setUser($this->getUser())
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
