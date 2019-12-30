<?php
namespace ApTeles\Database\Connector;

class SqliteConnection extends PDOConnection
{
    protected const REQUIRED_CONNECTION_KEYS= [
        'driver',
        'file',
        'username',
        'password',
        'default_fetch'
    ];

    protected function parseCredentials(array $credentials): array
    {
        $dsn = \sprintf(
            '%s:%s',
            $credentials['driver'],
            $credentials['file']
        );

        return [$dsn, $credentials['username'], $credentials['password']];
    }
}
