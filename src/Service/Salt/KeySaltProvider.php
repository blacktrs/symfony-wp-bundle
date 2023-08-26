<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Service\Salt;

class KeySaltProvider implements KeySaltProviderInterface
{
    public const SALT_KEYS = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT'
    ];

    public function __construct(private readonly SaltGeneratorInterface $saltGenerator)
    {
    }

    /**
     * @return array<string,string>
     */
    public function getKeySaltValues(): array
    {
        $values = [];
        foreach (self::SALT_KEYS as $key) {
            $values[sprintf('{%s}', $key)] = $this->saltGenerator->generate();
        }

        return $values;
    }
}
