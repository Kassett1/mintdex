<?php
namespace App\Command;

use App\Entity\Card;
use App\Service\CardEnricherService;
use App\Service\PokemonCardsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:enrich-cards',
    description: 'Enrichit les cartes avec les données détaillées (cardmarket, rareté, etc.)',
)]
class EnrichCardsCommand extends Command
{
    private PokemonCardsService $pokemonCardsService;
    private EntityManagerInterface $em;
    private CardEnricherService $enricher;

    public function __construct(
        PokemonCardsService $pokemonCardsService,
        EntityManagerInterface $em,
        CardEnricherService $enricher
    ) {
        parent::__construct();
        $this->pokemonCardsService = $pokemonCardsService;
        $this->em                  = $em;
        $this->enricher            = $enricher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des cartes à enrichir...');

        // On récupère uniquement celles qui ont besoin d’un enrichissement
        $cards = $this->em->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->where('c.cardmarketId IS NULL OR c.illustrator IS NULL')
            ->getQuery()
            ->getResult();

        $output->writeln(count($cards) . ' cartes à enrichir');

        $progressBar = new ProgressBar($output, count($cards));
        $progressBar->start();

        $batchSize = 50;
        $i         = 0;

        foreach ($cards as $card) {
            try {
                $data = $this->pokemonCardsService->fetchCardById($card->getSlug());
                // Enrichissement centralisé
                $this->enricher->enrich($card, $data);

            } catch (\Throwable $e) {
                // On skip si erreur API
                continue;
            }

            // batch flush
            if (($i % $batchSize) === 0) {
                $this->em->flush();
            }

            // évite le rate limit
            usleep(100000); // 0.1 sec

            $i++;
            $progressBar->advance();
        }

        $this->em->flush();

        $progressBar->finish();
        $output->writeln("\nEnrichissement terminé 👍");

        return Command::SUCCESS;
    }
}
