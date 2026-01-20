<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-tcgdex',
    description: 'Lance les différentes commandes liées aux données de l API Pokémon TCG API',
)]
class FetchTCGDexCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Synchronisation complète TCGDex');

        $commands = [
            'app:fetch-blocs',
            'app:fetch-sets',
            'app:fetch-cards',
        ];

        foreach ($commands as $commandName) {

            $io->section("Exécution de : $commandName");

            $command = $this->getApplication()->find($commandName);

            $result = $command->run(
                new ArrayInput([]),
                $output
            );

            if ($result !== Command::SUCCESS) {
                $io->error("La commande $commandName a échoué");
                return Command::FAILURE;
            }

            $io->success("$commandName terminé avec succès");
        }

        $io->success('Synchronisation TCGDex terminée 🎉');

        return Command::SUCCESS;
    }
}
