<?php

namespace App\Service;

use App\Entity\Card;

class CardEnricherService
{
    public function enrich(Card $card, array $data): void
    {
        // Cardmarket ID
        if (!$card->getCardmarketId() && !empty($data['pricing']['cardmarket']['idProduct'])) {
            $card->setCardmarketId($data['pricing']['cardmarket']['idProduct']);
        }

        // // Rareté
        // if (!$card->getRarity() && !empty($data['rarity'])) {
        //     $card->setRarity($data['rarity']);
        // }

        // // Illustrateur
        // if (!$card->getIllustrator() && !empty($data['illustrator'])) {
        //     $card->setIllustrator($data['illustrator']);
        // }
    }
}