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
    public function __construct(
        private PokemonCardsService $pokemonCardsService,
        private EntityManagerInterface $em,
        private CardEnricherService $enricher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des cartes à enrichir...');

        $query = $this->em->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->where('c.cardmarketId IS NULL OR c.illustrator IS NULL OR c.price IS NULL')
            ->getQuery();

        $cards = $query->toIterable();

        $total = (int) $this->em->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.cardmarketId IS NULL OR c.illustrator IS NULL OR c.price IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $output->writeln($total . ' cartes à enrichir');

        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        $batchSize = 50;
        $i = 0;

        foreach ($cards as $card) {
            try {
                $data = $this->pokemonCardsService->fetchCardById($card->getSlug());
                $this->enricher->enrich($card, $data);
            } catch (\Throwable $e) {
                $progressBar->advance();
                continue;
            }

            $i++;

            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            $progressBar->advance();
        }

        $this->em->flush();
        $this->em->clear();

        $progressBar->finish();
        $output->writeln("\nEnrichissement terminé 👍");

        return Command::SUCCESS;
    }
}