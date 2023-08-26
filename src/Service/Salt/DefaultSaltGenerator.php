<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Service\Salt;

use function strlen;

class DefaultSaltGenerator implements SaltGeneratorInterface
{
    public const LENGTH = 63;

    public function generate(): string
    {
        $symbols = "#$./_-+&;:^=!";
        $symbols .= implode('', range(0, 9));
        $symbols .= implode('', range('a', 'z'));
        $symbols .= strtoupper(implode('', range('a', 'z')));
        $symbols = str_shuffle($symbols);
        $key = [];
        for ($i = 0; $i < self::LENGTH; $i++) {
            $key[] = $symbols[random_int(0, strlen($symbols) - 1)];
        }

        return str_shuffle(implode('', $key));
    }
}
