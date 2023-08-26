<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Tests\Loader;

use Blacktrs\WPBundle\Loader\ConfigLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function constant;
use function defined;

class ConfigLoaderTest extends TestCase
{
    private const CONFIG_VALUES = [
        'WP_DEBUG' => true,
        'WP_HOME' => 'https://localhost',
        'parameters.values_test' => 'test',
    ];

    private ConfigLoader $configLoader;

    protected function setUp(): void
    {
        $this->configLoader = new ConfigLoader($this->createParameterBagMock());
    }

    public function testConfigLoader(): void
    {
        $this->configLoader->apply();

        static::assertTrue(defined('WP_DEBUG'));
        static::assertSame(self::CONFIG_VALUES['WP_DEBUG'], constant('WP_DEBUG'));

        static::assertTrue(defined('WP_HOME'));
        static::assertSame(self::CONFIG_VALUES['WP_HOME'], constant('WP_HOME'));

        static::assertFalse(defined('parameters.values_test'));

        static::assertTrue(defined('DB_HOST'));
        static::assertTrue(defined('DB_USER'));
        static::assertTrue(defined('DB_NAME'));
        static::assertTrue(defined('DB_PASSWORD'));
        static::assertTrue(defined('DB_CHARSET'));
    }

    private function createParameterBagMock(): ParameterBagInterface
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('all')->willReturn(self::CONFIG_VALUES);

        return $parameterBag;
    }
}
