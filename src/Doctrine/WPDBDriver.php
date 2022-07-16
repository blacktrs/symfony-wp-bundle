<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Doctrine;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\Mysqli\Connection;

class WPDBDriver extends AbstractMySQLDriver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params): Connection
    {
        return new Connection($GLOBALS['wpdb']->dbh);
    }
}
