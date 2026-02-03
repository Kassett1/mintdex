<?php

namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SetPageController extends AbstractController
{
    #[Route('/set/{slug}', name: 'app_set_page')]
    public function index(SetRepository $sr, CardRepository $cr, string $slug): Response
    {
        // UTILISER set->getCard() ici
        $selectedSet = $sr->findOneBy(['slug' => $slug]);
        $cards = $cr->findBy(['set' => $selectedSet]);

        return $this->render('set_page/index.html.twig', [
            'cards' => $cards,
        ]);
    }
}
