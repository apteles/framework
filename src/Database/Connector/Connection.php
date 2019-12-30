<?php
declare(strict_types=1);
namespace ApTeles\Database\Connector;

use InvalidArgumentException;
use ApTeles\Database\Contracts\ConnectionInterface;

abstract class Connection implements ConnectionInterface
{
    protected $connection;

    protected $credentials;

    protected const REQUIRED_CONNECTION_KEYS= [];

    public function setCredentials(array $credentials): bool
    {
        return $this->init($credentials);
    }

    private function init(array $credentials): bool
    {
        if (!$this->credentialsHaveRequiredKeys($credentials)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Database connection credentials are not mapped correctly, required key: %s',
                    \implode(',', static::REQUIRED_CONNECTION_KEYS)
                )
            );
        }

        $this->credentials = $credentials;

        return true;
    }

    private function credentialsHaveRequiredKeys(array $credentials): bool
    {
        $matcher = \array_intersect(static::REQUIRED_CONNECTION_KEYS, \array_keys($credentials));
        return \count($matcher) === \count(static::REQUIRED_CONNECTION_KEYS);
    }
}
