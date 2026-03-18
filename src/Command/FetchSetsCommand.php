<?php
namespace App\Command;

use App\Entity\Set;
use App\Repository\BlocRepository;
use App\Service\PokemonSetsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-sets',
    description: 'Récupère les sets de cartes depuis l API Pokémon TCG API',
)]
class FetchSetsCommand extends Command
{
    private PokemonSetsService $pokemonSetsService;
    private EntityManagerInterface $em;

    private BlocRepository $br;

    public function __construct(PokemonSetsService $pokemonSetsService, BlocRepository $br, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->pokemonSetsService = $pokemonSetsService;
        $this->em                 = $em;
        $this->br                 = $br;
    }

    private function pushSets(EntityManagerInterface $em, array $sets)
    {
        $blocList = $this->br->findAll();

        foreach ($sets as $s) {

            $detectedBloc = $this->pokemonSetsService->detectBloc($s, $blocList);

            if ($detectedBloc) {

                $set = new Set();

                $set->setSlug($s['id']);
                $set->setName($s['name']);

                // Définir avec le bloc ayant pour id le meme que le bloc détecté
                foreach ($blocList as $bloc) {
                    if ($bloc->getSlug() === $detectedBloc) {
                        $correspondingBloc = $bloc;
                        break;
                    }
                }
                $set->setBloc($correspondingBloc);

                if (isset($s['logo'])) {
                    $set->setLogo($s['logo']);
                }

                if (isset($s['symbol'])) {
                    $set->setSymbol($s['symbol']);
                }

                $set->setCardCount($s['cardCount']['official']);

                $em->persist($set);
            }
        }

        $em->flush();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des sets...');

        try {
            $sets = $this->pokemonSetsService->fetchSets();
            $output->writeln('OK : ' . count($sets) . ' sets récupérés');

            $this->pushSets($this->em, $sets);

            $output->writeln(messages: 'Sets sauvegardés 👍');

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur :</error> ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
