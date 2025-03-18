<?php

namespace App\Controller;

use App\Form\HtmlPdfUpload;
use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HtmlPdfController extends AbstractController
{
    private GotenbergService $pdfService;

    public function __construct(GotenbergService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generatePdfFromHtml(Request $request): Response
    {
        $form = $this->createForm(HtmlPdfUpload::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $htmlFile = $form->get('htmlFile')->getData();

            if ($htmlFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';

                $tempFileName = 'index.html';
                $tempFilePath = $uploadDir . $tempFileName;

                try {
                    $htmlFile->move($uploadDir, $tempFileName);
                } catch (FileException $e) {
                    return new Response("Erreur lors de l'upload du fichier : " . $e->getMessage(), 500);
                }

                $htmlContent = file_get_contents($tempFilePath);

                try {
                    // Passer le contenu HTML au service pour la génération du PDF
                    $pdfContent = $this->pdfService->generatePdfFromHtml($htmlContent);

                    // Génération du nom du fichier PDF
                    $pdfFileName = 'generated_pdf_' . uniqid() . '.pdf';
                    $pdfFilePath = '/pdf/' . $pdfFileName;

                    file_put_contents(
                        $this->getParameter('kernel.project_dir') . '/public' . $pdfFilePath,
                        $pdfContent
                    );

                    return $this->render('generate_pdf/show_pdf.html.twig', [
                        'pdfFilePath' => $pdfFilePath,
                    ]);
                } catch (\Exception $e) {
                    return new Response("Erreur lors de la génération du PDF : " . $e->getMessage(), 500);
                } finally {
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                }
            }
        }

        return $this->render('generate_pdf/upload_html.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
