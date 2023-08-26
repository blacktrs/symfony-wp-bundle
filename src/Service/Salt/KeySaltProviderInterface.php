<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Service\Salt;

interface KeySaltProviderInterface
{
    /**
     * @return array<string,string>
     */
    public function getKeySaltValues(): array;
}
