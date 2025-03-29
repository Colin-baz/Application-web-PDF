<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\HtmlPdfUpload;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class HtmlPdfController extends AbstractController
{
    private GotenbergService $pdfService;
    private EntityManagerInterface $entityManager;

    public function __construct(GotenbergService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    public function generatePdfFromHtml(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscription = $user->getSubscription();
        $maxPdfLimit = $subscription ? $subscription->getMaxPdf() : 0;
        $pdfGenerated = $this->entityManager->getRepository(File::class)
            ->countFilesGeneratedByUser($user->getId());

        $form = $this->createForm(HtmlPdfUpload::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($pdfGenerated >= $maxPdfLimit) {
                $this->addFlash('error', "Vous avez atteint votre limite de génération de PDFs (Max: $maxPdfLimit)");
                return $this->render('generate_pdf/upload_html.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $htmlFile = $form->get('htmlFile')->getData();

            if ($htmlFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';

                $tempFileName = 'index.html';
                $tempFilePath = $uploadDir . $tempFileName;

                try {
                    $htmlFile->move($uploadDir, $tempFileName);
                } catch (FileException $e) {
                    return new Response("Erreur lors de l'upload du fichier : " . $e->getMessage(), 500);
                }

                $htmlContent = file_get_contents($tempFilePath);

                try {
                    $pdfContent = $this->pdfService->generatePdfFromHtml($htmlContent);

                    $pdfFileName = 'generated_pdf_' . uniqid() . '.pdf';
                    $pdfFilePath = '/pdf/' . $pdfFileName;

                    file_put_contents(
                        $this->getParameter('kernel.project_dir') . '/public' . $pdfFilePath,
                        $pdfContent
                    );

                    $file = new File();
                    $file->setUser($user)
                        ->setName($pdfFileName)
                        ->setCreatedAt(new DateTime());

                    $this->entityManager->persist($file);
                    $this->entityManager->flush();

                    return $this->render('generate_pdf/show_pdf.html.twig', [
                        'pdfFilePath' => $pdfFilePath,
                    ]);
                } catch (\Exception $e) {
                    return new Response("Erreur lors de la génération du PDF : " . $e->getMessage(), 500);
                } finally {
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                }
            }
        }

        return $this->render('generate_pdf/upload_html.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

