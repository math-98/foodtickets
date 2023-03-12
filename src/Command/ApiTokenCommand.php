<?php

namespace App\Command;

use App\Entity\ApiToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'api:token',
    description: 'Generate a new API token',
)]
class ApiTokenCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->addArgument('user', InputArgument::OPTIONAL, 'User ID linked to the token');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getArgument('user');
        if (!$userId) {
            $userId = $io->ask('User ID linked to the token');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        $token = bin2hex(random_bytes(32));
        $tokenEntity = new ApiToken();
        $tokenEntity->setToken($token);
        $tokenEntity->setUser($user);
        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        $io->success("Token generated for user {$user->getName()}: {$token}");

        return Command::SUCCESS;
    }
}
