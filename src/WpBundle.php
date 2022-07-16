<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle;

use Blacktrs\WPBundle\Command\WPSaltsGeneratorCommand;
use Blacktrs\WPBundle\EventListener\RouterListener;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class WpBundle extends AbstractBundle
{
    /**
     * @param array<string,mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->load('Blacktrs\\WPBundle\\', '../src/*')
            ->exclude(['../src/stubs.php'])
            ->set('wp_bundle.listener', RouterListener::class)
            ->public()
            ->args(
                [
                    service('service_container'),
                    service('logger')->ignoreOnInvalid()
                ]
            )
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'request']);
    }

    public function registerCommands(Application $application)
    {
        $application->add(new WPSaltsGeneratorCommand());
    }
}
