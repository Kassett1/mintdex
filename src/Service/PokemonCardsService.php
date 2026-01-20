<?php
namespace App\Service;

use App\Repository\SetRepository;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonCardsService
{
    private HttpClientInterface $httpClient;

    private SetRepository $sr;

    public function __construct(HttpClientInterface $httpClient, SetRepository $sr)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://api.tcgdex.net/v2/fr/',
            // 'headers'  => [
            //     'X-Api-Key' => $_ENV['POKEMON_TCG_API_KEY'] ?? '',
            // ],
        ]);

        $this->sr = $sr;
    }

    public function fetchCards(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'cards');
            return $response->toArray();

        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Erreur lors de l’appel API', 0, $e);

        } catch (HttpExceptionInterface $e) {
            throw $e;
        }
    }

    public function detectSet(array $card)
    {
        // 1. On extrait le préfixe avant le premier chiffre
        $prefix = explode('-', $card['id'])[0];

        // 2. Détection TCGP → on récupère pas
        if (preg_match('/^[A-Z]\d[a-z]?$/', $prefix)) {
            return null;
        }

        // 3. On cherche dans les sets
        $set = $this->sr->findOneBy(['slug' => $prefix]);

        if ($set) {
            return $set;
        }

        return null;
    }

}
