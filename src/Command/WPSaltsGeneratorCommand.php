<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Command;

use Blacktrs\WPBundle\Service\Salt\KeySaltProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WPSaltsGeneratorCommand extends Command
{
    public function __construct(private readonly KeySaltProviderInterface $keySaltProvider, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('modernwp:salts:generate')
            ->setDescription('Generates salts in .env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        /** @var \Symfony\Bundle\FrameworkBundle\Console\Application $application */
        $application = $this->getApplication();
        $projectDir = $application->getKernel()->getProjectDir();
        $env = $projectDir . '/.env.local';

        if (!is_file($env)) {
            $output->error('[notice] No .env.local file found');

            return Command::FAILURE;
        }

        $envData = strtr(file_get_contents($env), $this->keySaltProvider->getKeySaltValues());
        $output->success('[notice] Created salts env variables');

        file_put_contents($env, $envData);

        return Command::SUCCESS;
    }
}
