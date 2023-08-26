<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Service\Salt;

interface SaltGeneratorInterface
{
    public function generate(): string;
}
