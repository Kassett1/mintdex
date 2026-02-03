<?php
namespace App\Controller;

use App\Entity\UserCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveCardController extends AbstractController
{
    #[Route('/collection/remove/card', name: 'app_remove_card', methods: ['POST'])]
    public function removeCard(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // 1. vérifier qu'on a un utilisateur connecté
        $user = $this->getUser() ?? null;
        if (! $user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        // 2. récupérer la carte
        $cardId = $data['cardId'] ?? null;
        if (! $cardId) {
            return new JsonResponse(['success' => false, 'message' => 'Pas de carte spécifiée'], 400);
        }

        $card = $em->getRepository(UserCard::class)->findOneBy([
            'id'   => $cardId,
            'user' => $user,
        ]);
        if (! $card) {
            return new JsonResponse(['success' => false, 'message' => 'Carte introuvable'], 404);
        }

        // 3. supprimer la UserCard
        $em->remove($card);
        $em->flush();

        // 4. renvoyer JSON OK
        return new JsonResponse(['success' => true]);
    }
}
