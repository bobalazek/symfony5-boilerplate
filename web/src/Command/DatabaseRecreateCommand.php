<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseRecreateCommand extends Command
{
    protected static $defaultName = 'app:database:recreate';

    protected function configure()
    {
        $this
            ->setDescription('Recreates the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting to recreate the database ...</info>');

        $commands = [
            [
                'command' => 'doctrine:schema:drop',
                'arguments' => [
                    '--force' => true,
                ],
                'description' => 'Dropping the database schema ...',
            ],
            [
                'command' => 'doctrine:schema:update',
                'arguments' => [
                    '--force' => true,
                ],
                'description' => 'Updating the database schema ...',
            ],
            [
                'command' => 'doctrine:fixtures:load',
                'arguments' => [
                    '--no-interaction' => true,
                ],
                'description' => 'Loading the fixtures ...',
            ],
        ];
        foreach ($commands as $entry) {
            $output->writeln($entry['description']);

            $command = $this->getApplication()->find($entry['command']);

            $input = new ArrayInput($entry['arguments'] ?? []);
            $input->setInteractive(false);

            $command->run($input, $output);
        }

        $output->writeln('<info>You have successfully created the database!</info>');

        return Command::SUCCESS;
    }
}
