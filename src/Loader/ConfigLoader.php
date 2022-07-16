<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\Loader;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConfigLoader
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    /**
     * @throws \ErrorException
     */
    public function apply(): void
    {
        foreach ($this->parameterBag->all() as $parameter => $value) {
            if (ctype_upper(preg_replace('/[^a-z]/i', '', $parameter)) && !\defined($parameter)) {
                \define($parameter, $value);
            }
        }

        $dsn = parse_url($_ENV['DATABASE_URL'] ?? '');

        if (!$dsn) {
            throw new \ErrorException('Cannot parse database string');
        }

        parse_str($dsn['query'] ?? '', $dsnQuery);

        \define('DB_HOST', $dsn['host'].':'.($dsn['port'] ?? 3306));
        \define('DB_USER', $dsn['user']);
        \define('DB_NAME', trim($dsn['path'], '/'));
        \define('DB_PASSWORD', $dsn['pass']);
        \define('DB_CHARSET', $dsnQuery['charset']);
    }
}
