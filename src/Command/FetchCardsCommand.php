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
    description: 'Récupère les cartes depuis l API Pokémon TCG API',
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

    private function pushCards(EntityManagerInterface $em, array $cards, OutputInterface $output)
    {
        $batchSize = 200;
        $i = 0;

        $progressBar = new ProgressBar($output, count($cards));
        $progressBar->start();

        foreach ($cards as $c) {

            $set = $this->pokemonCardsService->detectSet($c);

            // si null → on ignore (TCGP ou set inconnu)
            if (! $set) {
                $progressBar->advance();
                continue;
            }

            $card = new Card();

            $card->setName($c['name']);
            $card->setSlug($c['id']);
            $card->setLocalId($c['localId']);

            if (! empty($c['image'])) {
                $card->setImage($c['image']);
            }

            $card->setSet($set);

            $em->persist($card);

            // 🟢 BATCH FLUSH
            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear(Card::class); // libère la mémoire
            }

            $i++;
            $progressBar->advance();
        }

        // flush final
        $em->flush();
        $em->clear(Card::class);

        $progressBar->finish();
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des cartes...');

        try {
            $cards = $this->pokemonCardsService->fetchCards();
            $output->writeln('OK : ' . count($cards) . ' cartes récupérés');

            $this->pushCards($this->em, $cards, $output);

            $output->writeln(messages: 'Cartes sauvegardés 👍');

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur :</error> ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
