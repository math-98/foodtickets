<?php

namespace App\Command;

use Bugsnag\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'bugsnag:deploy',
    description: 'Notify Bugsnag of a new deployment',
)]
class BugsnagDeployCommand extends Command
{
    private Client $bugsnag;

    public function __construct(Client $bugsnag)
    {
        parent::__construct();
        $this->bugsnag = $bugsnag;
    }

    protected function configure(): void
    {
        $this
            ->addOption('repository', null, InputOption::VALUE_REQUIRED, 'Git repository URL')
            ->addOption('revision', null, InputOption::VALUE_REQUIRED, 'Git revision')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bugsnag->build(
            repository: $input->getOption('repository'),
            revision: $input->getOption('revision'),
        );

        $io->success('Bugsnag notified of new deployment');

        return Command::SUCCESS;
    }
}
