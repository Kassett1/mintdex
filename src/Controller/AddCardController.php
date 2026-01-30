<?php
namespace App\Controller;

use App\Entity\Card;
use App\Entity\UserCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AddCardController extends AbstractController
{
    #[Route('/collection/add/card', name: 'app_add_card', methods: ['POST'])]
    public function addCard(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // 1. vérifier qu'on a un utilisateur connecté
        $user = $this->getUser();
        if (! $user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        // 2. récupérer la carte
        $cardId = $data['cardId'];
        if (! $cardId) {
            return new JsonResponse(['success' => false, 'message' => 'Pas de carte spécifiée'], 400);
        }

        $card = $em->getRepository(Card::class)->find($cardId);
        if (! $card) {
            return new JsonResponse(['success' => false, 'message' => 'Carte introuvable'], 404);
        }

        // 3. créer la nouvelle entité UserCard
        $userCard = new UserCard();
        $userCard->setUser($user);
        $userCard->setCard($card);
        $userCard->setCardCondition($data['condition']);

        // 4. persister en BDD
        $em->persist($userCard);
        $em->flush();

        // 5. renvoyer JSON OK
        return new JsonResponse(['success' => true]);
    }
}
