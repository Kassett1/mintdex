<?php
namespace App\Command;

use App\Entity\Bloc;
use App\Repository\BlocRepository;
use App\Service\PokemonBlocsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-blocs',
    description: 'Récupère les blocs de cartes depuis l API Pokémon TCG API',
)]
class FetchBlocsCommand extends Command
{
    private PokemonBlocsService $pokemonBlocsService;
    private EntityManagerInterface $em;

    private BlocRepository $blocRepo;

    public function __construct(
        PokemonBlocsService $pokemonBlocsService,
        EntityManagerInterface $em,
        BlocRepository $blocRepo,
        BlocRepository $br
    ) {
        parent::__construct();

        $this->pokemonBlocsService = $pokemonBlocsService;
        $this->em                  = $em;
        $this->blocRepo            = $blocRepo;
    }

    // private function clearTables()
    // {
    //     $connection = $this->em->getConnection();
    //     $platform   = $connection->getDatabasePlatform();

    //     // on supprime d'abord les sets
    //     $connection->executeStatement(
    //         $platform->getTruncateTableSQL('set', true)
    //     );

    //     // puis les blocs
    //     $connection->executeStatement(
    //         $platform->getTruncateTableSQL('bloc', true)
    //     );
    // }

    // private function pushBlocs(EntityManagerInterface $em, array $blocs)
    // {
    //     foreach ($blocs as $b) {

    //         $bloc = new Bloc();

    //         $bloc->setSlug($b['id']);
    //         $bloc->setName($b['name']);
    //         $bloc->setSourceIds($b['sourceIds']);

    //         $em->persist($bloc);
    //     }

    //     $em->flush();
    // }

    private function pushBlocs(EntityManagerInterface $em, array $blocs, BlocRepository $blocRepo)
    {
        foreach ($blocs as $b) {

            // On cherche si le bloc existe déjà
            $bloc = $blocRepo->findOneBy(['slug' => $b['id']]);

            // Sinon on le crée
            if (! $bloc) {
                $bloc = new Bloc();
                $bloc->setSlug($b['id']);
                $bloc->setName($b['name']);
                $bloc->setSourceIds($b['sourceIds']);
            }

            $em->persist($bloc);
        }

        $em->flush();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Récupération des blocs...');

        try {
            $blocs = $this->pokemonBlocsService->fetchBlocs();
            $output->writeln('OK : ' . count(value: $blocs) . ' blocs récupérés');

            // $this->clearTables();
            $this->pushBlocs($this->em, $blocs, $this->blocRepo);

            $output->writeln('Blocs sauvegardés 👍');

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur :</error> ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
