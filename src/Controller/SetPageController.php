<?php
namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\SetRepository;
use App\Repository\UserCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SetPageController extends AbstractController
{
    #[Route('/set/{slug}', name: 'app_set_page')]
    public function index(SetRepository $sr, CardRepository $cr, UserCardRepository $ucr, string $slug): Response
    {
        // UTILISER set->getCard() ici
        $selectedSet = $sr->findOneBy(['slug' => $slug]);
        $cards       = $cr->findBy(['set' => $selectedSet]);

        $user = $this->getUser();
        $set  = $sr->findOneBy(['slug' => $slug]);

        $ownedCards = [];
        if ($user) {
            $userCards = $ucr->findOwnedCardsBySet($user, $set);
            foreach ($userCards as $uc) {
                $ownedCards[] = $uc->getCard()->getId();
            }
        }

        return $this->render('set_page/index.html.twig', [
            'cards' => $cards,
            'ownedCards' => $ownedCards,
        ]);
    }
}
