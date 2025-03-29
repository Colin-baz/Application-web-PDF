<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\HtmlPdfUpload;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class HtmlPdfController extends AbstractController
{
    private GotenbergService $pdfService;
    private EntityManagerInterface $entityManager;

    public function __construct(GotenbergService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    public function generatePdfFromHtml(Request $request): Response
    {
        $form = $this->createForm(HtmlPdfUpload::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('generate_pdf/upload_html.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $htmlFile = $form->get('htmlFile')->getData();
        if ($htmlFile) {
            return $this->handleFileUpload($htmlFile);
        }

        return $this->render('generate_pdf/upload_html.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function handleFileUpload($htmlFile): Response
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        $tempFileName = 'index.html';
        $tempFilePath = $uploadDir . $tempFileName;

        try {
            $this->uploadHtmlFile($htmlFile, $uploadDir, $tempFileName);
            $htmlContent = file_get_contents($tempFilePath);
            return $this->generatePdfFromHtmlContent($htmlContent, $tempFilePath);
        } catch (FileException $e) {
            return new Response("Erreur lors de l'upload du fichier : " . $e->getMessage(), 500);
        }
    }

    private function uploadHtmlFile($htmlFile, $uploadDir, $tempFileName): void
    {
        $htmlFile->move($uploadDir, $tempFileName);
    }

    private function generatePdfFromHtmlContent(string $htmlContent, string $tempFilePath): Response
    {
        try {
            $pdfContent = $this->pdfService->generatePdfFromHtml($htmlContent);

            $pdfFileName = 'generated_pdf_' . uniqid() . '.pdf';
            $pdfFilePath = '/pdf/' . $pdfFileName;

            file_put_contents(
                $this->getParameter('kernel.project_dir') . '/public' . $pdfFilePath,
                $pdfContent
            );

            $this->saveGeneratedPdf($pdfFileName);

            return $this->render('generate_pdf/show_pdf.html.twig', [
                'pdfFilePath' => $pdfFilePath,
            ]);
        } catch (\Exception $e) {
            return new Response("Erreur lors de la génération du PDF : " . $e->getMessage(), 500);
        }
    }

    private function saveGeneratedPdf(string $pdfFileName): void
    {
        $file = new File();
        $file->setUser($this->getUser())
            ->setName($pdfFileName)
            ->setCreatedAt(new DateTime());

        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}
