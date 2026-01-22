<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardPageController extends AbstractController
{
    #[Route('/card/{slug}', name: 'app_card_page')]
    public function index(CardRepository $cr, string $slug): Response
    {
        $card = $cr->findOneBy(['slug' => $slug]);

        return $this->render('card_page/index.html.twig', [
            'card' => $card,
        ]);
    }
}
