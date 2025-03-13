<?php

// src/Controller/HtmlPdfController.php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HtmlPdfController extends AbstractController
{
    private $gotenbergService;

    public function __construct(GotenbergService $gotenbergService)
    {
        $this->gotenbergService = $gotenbergService;
    }

    /**
     * @Route("/generate-pdf-from-html", name="generate_pdf_from_html", methods={"GET"})
     */
    public function generatePdfFromHtml(): Response
    {
        try {
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Mon Document HTML</title>
                </head>
                <body>
                    <h1>Bonjour, ceci est un document HTML</h1>
                    <p>Ce contenu sera converti en PDF.</p>
                </body>
                </html>
            ';

            $pdfContent = $this->gotenbergService->generatePdfFromHtml($htmlContent);
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="document.pdf"',
            ]);
        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
    }
}
