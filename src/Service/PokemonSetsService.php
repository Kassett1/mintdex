<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonSetsService
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

    public function fetchSets(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'sets');
            return $response->toArray();

        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Erreur lors de l’appel API', 0, $e);

        } catch (HttpExceptionInterface $e) {
            throw $e;
        }
    }

    public function detectBloc(array $set, array $blocList)
    {
        // 1. On extrait le préfixe avant le premier chiffre
        $prefix = preg_replace('/\d.*$/', '', $set['id']);

        // 2. Si longueur 1 → c’est du TCGP → on récupère pas
        if (strlen($prefix) === 1) {
            return null;
        }

        // 3. On cherche dans les blocs
        foreach ($blocList as $bloc) {

            foreach ($bloc->getSourceIds() as $sourceId) {

                if ($prefix === $sourceId) {
                    return $bloc->getSlug();
                }
            }
        }

        // 4. Si aucun bloc trouvé → fallback misc
        return 'misc';
    }

}
