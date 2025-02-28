<?php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    public function __construct(private GotenbergService $gotenbergService)
    {
    }

    public function generatePdf(): Response
    {
        $htmlContent = '<h1>Générateur de PDF</h1>';
        $pdfContent = $this->gotenbergService->generatePdf($htmlContent);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
