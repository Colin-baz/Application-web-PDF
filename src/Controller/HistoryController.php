<?php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $files = $entityManager->getRepository(File::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('history/index.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route('/history/view/{id}', name: 'history_view')]
    public function viewPdf(File $file): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($file->getUser() !== $user) {
            throw $this->createAccessDeniedException("Access Denied.");
        }

        return $this->render('history/show_pdf.html.twig', [
            'file' => $file,
        ]);
    }

    #[Route('/history/download/{id}', name: 'history_download')]
    public function downloadPdf(File $file): BinaryFileResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($file->getUser() !== $user) {
            throw $this->createAccessDeniedException("Access Denied.");
        }

        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/' . $file->getName();

        if (!file_exists($pdfPath)) {
            throw $this->createNotFoundException("Le fichier n'existe pas.");
        }

        return new BinaryFileResponse($pdfPath, 200, [], true, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
