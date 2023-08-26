<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Tests\Service\Salt;

use Blacktrs\WPBundle\Service\Salt\{DefaultSaltGenerator, KeySaltProvider};
use PHPUnit\Framework\TestCase;

use function count;

class KeySaltProviderTest extends TestCase
{
    private KeySaltProvider $keySaltProvider;

    protected function setUp(): void
    {
        $this->keySaltProvider = new KeySaltProvider(new DefaultSaltGenerator());
    }

    public function testKeySaltProviderValues(): void
    {
        $values = $this->keySaltProvider->getKeySaltValues();

        static::assertCount(count(KeySaltProvider::SALT_KEYS), $values);

        foreach (KeySaltProvider::SALT_KEYS as $key) {
            static::assertArrayHasKey(sprintf('{%s}', $key), $values);
        }
    }
}
