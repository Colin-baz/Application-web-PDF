<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use RuntimeException;

class GotenbergService
{
    private HttpClientInterface $httpClient;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $httpClient, string $gotenbergUrl)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = rtrim($gotenbergUrl, '/'); // Assurer que l'URL est bien formatée
    }

    public function generatePdfFromUrl(string $url): string
    {
        if (empty($url)) {
            throw new RuntimeException('L’URL fournie est vide.');
        }

        try {
            dump("Envoi de l'URL à Gotenberg : " . $url);

            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'url' => $url,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $errorMessage = $response->getContent(false); // Récupérer la réponse en cas d'erreur
                throw new RuntimeException("Erreur HTTP $statusCode lors de la génération du PDF : " . $errorMessage);
            }

            return $response->getContent();
        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }
}
