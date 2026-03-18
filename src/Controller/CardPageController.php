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
            $userCards = $wc->findBy(['user' => $user]);
            foreach ($userCards as $w) {
                $wishlistCards[] = $w->getCard()->getId();
            }
        }

        function formatNameForURL($name)
        {
            // Enlève les accents
            $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);

            // Supprime les caractères spéciaux et remplace par espace
            $cleaned = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $name);

            // Remplace les espaces par +
            return trim(preg_replace('/\s+/', '+', $cleaned));
        }

        $cardNameURL = formatNameForURL($card->getName());
        $cardNumber  = $card->getLocalId() . '%2F' . $card->getset()->getCardCount();

        $cardmarketLink = "https://www.cardmarket.com/Pokemon/Products?idProduct={$card->getCardmarketId()}";
        $ebayLink       = "";
        $vintedLink     = "https://www.vinted.fr/catalog?search_text={$cardNameURL}+{$cardNumber}";

        return $this->render('card_page/index.html.twig', [
            'card'           => $card,
            'cards'          => $cards,
            'wishlistCards'  => $wishlistCards,
            'page'           => "Carte",
            'cardmarketLink' => $cardmarketLink,
            'ebayLink'       => $ebayLink,
            'vintedLink'     => $vintedLink,
        ]);
    }
}
