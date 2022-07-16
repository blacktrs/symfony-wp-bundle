<?php

namespace Blacktrs\WPBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WPSaltsGeneratorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('modernwp:salts:generate')
            ->setDescription('Generates salts in .env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = $this->getApplication()->getKernel()->getProjectDir();
        $env = $projectDir . '/.env';

        if (!is_file($env)) {
            $output->writeln('[notice] Not .env file found');

            return Command::SUCCESS;
        }

        $this->createSalts($env, $output);

        return Command::SUCCESS;
    }

    public function createSalts(string $env, OutputInterface $output): void
    {
        $keys = [
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        ];

        $envData = file_get_contents($env);
        foreach ($keys as $key) {
            $envData = str_replace(sprintf('{%s}', $key), self::createRandomString(), $envData);
        }

        $output->writeln('[notice] Created salts env variables');
        file_put_contents($env, $envData);
    }

    private static function createRandomString(): string
    {
        $symbols = "#$./_-+&;:^=!";
        $symbols .= implode('', range(0, 9));
        $symbols .= implode('', range('a', 'z'));
        $symbols .= strtoupper(implode('', range('a', 'z')));
        $symbols = str_shuffle($symbols);
        $key = [];
        for ($i = 0; $i < 63; $i++) {
            $key[] = $symbols[random_int(0, strlen($symbols) - 1)];
        }

        return str_shuffle(implode('', $key));
    }
}