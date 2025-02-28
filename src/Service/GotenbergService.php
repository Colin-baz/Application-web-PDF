<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergService
{
    public function __construct(private HttpClientInterface $client, private string $gotenbergUrl)
    {
    }

    public function generatePdf(string $htmlContent): string
    {
        $response = $this->client->request('POST', $this->gotenbergUrl . '/convert/html', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'files' => [
                    [
                        'name' => 'document.html',
                        'contents' => $htmlContent,
                    ],
                ],
            ],
        ]);

        return $response->getContent();
    }
}
