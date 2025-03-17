<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class GotenbergService
{
    private HttpClientInterface $httpClient;
    private string $gotenbergUrl;
    private string $uploadDir;

    public function __construct(HttpClientInterface $httpClient, string $gotenbergUrl)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = rtrim($gotenbergUrl, '/');
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
    }

    /**
     * Générer un PDF à partir d'une URL
     */
    public function generatePdfFromUrl(string $url): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => ['url' => $url],
            ]);


            return $response->getContent();
        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de la génération du PDF depuis une URL : ' . $e->getMessage());
        }
    }

    /**
     * Générer un PDF à partir d'un fichier HTML
     */
    public function generatePdfFromHtml(string $htmlContent): string
    {
        $fileName = 'index.html';
        $tempFile = $this->uploadDir . $fileName;

        file_put_contents($tempFile, $htmlContent);

        // Vérification de la création du fichier
        if (!file_exists($tempFile)) {
            throw new RuntimeException("Le fichier temporaire n'a pas été créé : " . $tempFile);
        }

        try {
            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'files' => fopen($tempFile, 'r'),
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                $responseContent = $response->getContent();
                throw new RuntimeException('Gotenberg a renvoyé une erreur HTTP ' . $response->getStatusCode() . ' : ' . $responseContent);
            }

            $pdfContent = $response->getContent();

        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de la génération du PDF : ' . $e->getMessage());
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $pdfContent;
    }
}
