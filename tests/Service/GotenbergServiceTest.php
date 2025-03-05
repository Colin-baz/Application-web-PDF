<?php

namespace App\Tests\Service;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GotenbergServiceTest extends WebTestCase
{
    public function testGeneratePdfFromHtml()
    {
        $mockClient = $this->createMock(HttpClientInterface::class);

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn('PDF content');

        $mockClient
            ->method('request')
            ->willReturn($mockResponse);

        $service = new GotenbergService($mockClient, 'http://localhost:3000');

        $pdfContent = $service->generatePdfFromHtml('<h1>Hello</h1>');

        $this->assertEquals('PDF content', $pdfContent);
    }
}
