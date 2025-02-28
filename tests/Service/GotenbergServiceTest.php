<?php

namespace App\Tests\Service;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergServiceTest extends WebTestCase
{
    public function testGeneratePdf()
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient
            ->method('request')
            ->willReturn(new Response(['content' => 'PDF content'], 200));

        $service = new GotenbergService($mockClient, 'http://localhost:3000');
        $pdfContent = $service->generatePdf('<h1>Hello</h1>');

        $this->assertEquals('PDF content', $pdfContent);
    }
}
