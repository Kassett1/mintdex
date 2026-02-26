<?php
namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\UserCardRepository;
use App\Repository\WishlistCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardPageController extends AbstractController
{
    #[Route('/card/{slug}', name: 'app_card_page')]
    public function index(CardRepository $cr, UserCardRepository $ucr, string $slug, WishlistCardRepository $wc): Response
    {
        $card = $cr->findOneBy(['slug' => $slug]);

        $user = $this->getUser();

        $cards = $user
            ? $ucr->findBy(['user' => $user, 'card' => $card])
            : null;

        $wishlistCards = [];
        if ($user) {
            $userCards = $wc->findAll();
            foreach ($userCards as $w) {
                $wishlistCards[] = $w->getCard()->getId();
            }
        }

        return $this->render('card_page/index.html.twig', [
            'card'  => $card,
            'cards' => $cards,
            'wishlistCards' => $wishlistCards,
        ]);
    }
}
