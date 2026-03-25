<?php
namespace App\Command;

use App\Entity\Card;
use App\Service\PokemonCardsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-cards',
    description: 'Récupère et synchronise les cartes depuis l API Pokémon TCG',
)]
class FetchCardsCommand extends Command
{
    public function __construct(
        private PokemonCardsService $pokemonCardsService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    private function pushCards(array $cards, OutputInterface $output): void
    {
        $batchSize = 200;
        $i         = 0;

        $progressBar = new ProgressBar($output, count($cards));
        $progressBar->start();

        // On charge seulement les infos minimales utiles pour savoir si une carte existe déjà
        $rows = $this->em->createQuery('
            SELECT c.id, c.slug, c.localId
            FROM App\Entity\Card c
        ')->getArrayResult();

        $cardsMap = [];
        foreach ($rows as $row) {
            $key            = $row['slug'] . '_' . $row['localId'];
            $cardsMap[$key] = $row['id'];
        }

        $cardRepository = $this->em->getRepository(Card::class);

        foreach ($cards as $c) {
            try {
                $set = $this->pokemonCardsService->detectSet($c);

                // Ignore les cartes sans set
                if (! $set) {
                    $progressBar->advance();
                    continue;
                }

                $slug    = $c['id'] ?? null;
                $localId = $c['localId'] ?? null;

                if (! $slug || ! $localId) {
                    $progressBar->advance();
                    continue;
                }

                // Clé unique logique
                $key = $slug . '_' . $localId;

                if (isset($cardsMap[$key])) {
                    $card = $cardRepository->find($cardsMap[$key]);
                } else {
                    // CREATE
                    $card = new Card();
                    $this->em->persist($card);
                }

                $card->setName($c['name'] ?? '');
                $card->setSlug($slug);
                $card->setLocalId($localId);

                if (! empty($c['image'])) {
                    $card->setImage($c['image']);
                }

                $card->setSet($set);

                $i++;

                // Flush par batch
                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                    $cardRepository = $this->em->getRepository(Card::class);
                }
            } catch (\Throwable $e) {
                $output->writeln(
                    '<error>Erreur carte ' . ($c['id'] ?? 'unknown') . ' : ' . $e->getMessage() . '</error>'
                );
                continue;
            }

            $progressBar->advance();
        }

        $this->em->flush();
        $this->em->clear();

        $progressBar->finish();
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des cartes...');

        try {
            $cards = $this->pokemonCardsService->fetchCards();
            $output->writeln('OK : ' . count($cards) . ' cartes récupérées');

            $this->pushCards($cards, $output);

            $output->writeln('Cartes synchronisées 👍');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur :</error> ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
