<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScanCardController extends AbstractController
{
    #[Route('/scan', name: 'app_scan_card')]
    public function index(): Response
    {
        return $this->render('scan_card/index.html.twig', [
            'controller_name' => 'ScanCardController',
            'page' => "Scan"
        ]);
    }
}
