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
    private PokemonCardsService $pokemonCardsService;
    private EntityManagerInterface $em;

    public function __construct(PokemonCardsService $pokemonCardsService, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->pokemonCardsService = $pokemonCardsService;
        $this->em                  = $em;
    }

    private function pushCards(array $cards, OutputInterface $output)
    {
        $batchSize = 200;
        $i         = 0;

        $progressBar = new ProgressBar($output, count($cards));
        $progressBar->start();

        // 1. Charger toutes les cartes existantes en mémoire (OPTIMISATION)
        $existingCards = $this->em->getRepository(Card::class)->findAll();

        $cardsMap = [];
        foreach ($existingCards as $existing) {
            $key            = $existing->getSlug() . '_' . $existing->getLocalId();
            $cardsMap[$key] = $existing;
        }

        foreach ($cards as $c) {

            $set = $this->pokemonCardsService->detectSet($c);

            // Ignore les cartes sans set
            if (! $set) {
                $progressBar->advance();
                continue;
            }

            // Clé unique pour identifier une carte
            $key = $c['id'] . '_' . $c['localId'];

            // 2. Vérifie si la carte existe déjà
            if (isset($cardsMap[$key])) {
                $card = $cardsMap[$key]; // UPDATE
            } else {
                $card = new Card(); // CREATE
                $this->em->persist($card);
            }

            // 3. Mise à jour des données (update OU create)
            $card->setName($c['name']);
            $card->setSlug($c['id']);
            $card->setLocalId($c['localId']);
            if (! empty($c['image'])) {
                $card->setImage($c['image']);
            }
            $card->setSet($set);

            // BATCH FLUSH pour éviter surcharge mémoire
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            $i++;
            $progressBar->advance();
        }

        // flush final
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
