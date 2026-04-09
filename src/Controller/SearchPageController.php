<?php
namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\UserCardRepository;
use App\Repository\WishlistCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchPageController extends AbstractController
{
    #[Route('/search', name: 'app_search_page')]
    public function index(Request $request, CardRepository $cr,UserCardRepository $ucr, WishlistCardRepository $wc): Response
    {
        $query = trim($request->query->get('q', ''));

        $user = $this->getUser();

        if ($query === '') {
            return $this->redirectToRoute('app_home');
        }

        $cards = $cr->searchByNameOrIllustrator($query);

        $ownedCards = [];
        if ($user) {
            $userCards = $ucr->findOwnedCardsByQuery($user, $query);
            foreach ($userCards as $uc) {
                $ownedCards[] = $uc->getCard()->getId();
            }
        }

        $wishlistCards = [];
        if ($user) {
            $userCards = $wc->findWishlistCardsByQuery($user, $query);
            foreach ($userCards as $w) {
                $wishlistCards[] = $w->getCard()->getId();
            }
        }

        return $this->render('search_page/index.html.twig', [
            'cards' => $cards,
            'page'  => "Recherche",
            'query' => $query,
            'ownedCards'    => $ownedCards,
            'wishlistCards' => $wishlistCards,
        ]);
    }
}
