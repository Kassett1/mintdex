<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonBlocsService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://api.tcgdex.net/v2/fr/',
            // 'headers'  => [
            //     'X-Api-Key' => $_ENV['POKEMON_TCG_API_KEY'] ?? '',
            // ],
        ]);
    }

    public function fetchBlocs(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'series');
            // var_dump($this->normalizeBlocs($response->toArray()));
            return $this->normalizeBlocs($response->toArray());

        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Erreur lors de l’appel API', 0, $e);

        } catch (HttpExceptionInterface $e) {
            throw $e;
        }
    }

    private function normalizeBlocs(array $blocs): array
    {
        // 1. Indexation par ID
        $indexed = [];
        foreach ($blocs as $bloc) {
            $bloc['sourceIds']    = [$bloc['id']];
            $indexed[$bloc['id']] = $bloc;
        }

        // 2. Création bloc Wizards
        $wizardsBloc = [
            'id'        => 'wizards',
            'name'      => 'Wizards',
            'sourceIds' => ['base', 'neo', 'ecard'],
        ];

        // 3. Organisation de l'ordre des blocs

        // Ordre manuel du début
        $baseOrder = ["misc", "tk", "pop", "mc", "wizards"];

        // Tous les IDs existants dans l'API
        $allIds = array_keys($indexed);

        // On retire ceux déjà gérés à la main et ceux que l'on ne veut pas garder
        $remaining = array_diff($allIds, ["base", "neo", "ecard", "misc", "tk", "pop", "mc", "tcgp"]);

        // On garde l'ordre naturel de l’API
        $order = array_merge($baseOrder, $remaining);

        // 4. Construction finale
        $orderedBlocs = [];

        foreach ($order as $id) {

            if ($id === 'wizards') {
                $orderedBlocs[] = $wizardsBloc;
                continue;
            }

            $orderedBlocs[] = $indexed[$id];
        }

        return $orderedBlocs;
    }

}
