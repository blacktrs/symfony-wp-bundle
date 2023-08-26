<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Tests\Service\Salt;

use Blacktrs\WPBundle\Service\Salt\DefaultSaltGenerator;
use PHPUnit\Framework\TestCase;

use function strlen;

class DefaultSaltGeneratorTest extends TestCase
{
    private DefaultSaltGenerator $defaultSaltGenerator;

    protected function setUp(): void
    {
        $this->defaultSaltGenerator = new DefaultSaltGenerator();
    }

    public function testSaltGenerator(): void
    {
        $salt = $this->defaultSaltGenerator->generate();

        static::assertIsString($salt);
        static::assertSame(strlen($salt), DefaultSaltGenerator::LENGTH);
    }
}
