<?php

// src/Service/GotenbergService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergService
{
    private $httpClient;
    private $gotenbergUrl;

    public function __construct(HttpClientInterface $httpClient, string $gotenbergUrl)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = $gotenbergUrl;
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {
        try {

            $tempFilePath = sys_get_temp_dir() . '/index.html';
            file_put_contents($tempFilePath, $htmlContent);

            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'files' => [
                        'file' => fopen($tempFilePath, 'r'),
                    ],
                ],
            ]);


            unlink($tempFilePath);

            return $response->getContent();
        } catch (\Exception $e) {

            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            throw new \RuntimeException('Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }
}



