<?php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    private $gotenbergService;

    public function __construct(GotenbergService $gotenbergService)
    {
        $this->gotenbergService = $gotenbergService;
    }

    /**
     * @Route("/generate-pdf", name="generate_pdf")
     */
    public function generatePdf(): Response
    {
        try {
            $url = 'https://sparksuite.github.io/simple-html-invoice-template/';
            $pdfContent = $this->gotenbergService->generatePdf($url);
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
    }
}
