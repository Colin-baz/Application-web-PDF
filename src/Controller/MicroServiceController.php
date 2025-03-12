<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MicroServiceController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    /**
     * @Route("/test-microservice", name="test_microservice")
     */
    public function fetchGitHubInformation(): JsonResponse
    {
        $response = $this->client->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );

        $content = $response->toArray();

        return new JsonResponse([
            'status' => 'success',
            'data' => $content,
        ]);
    }
}
