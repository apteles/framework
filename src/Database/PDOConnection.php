<?php
namespace ApTeles\Database;

use PDO;
use RuntimeException;
use ApTeles\Database\Contracts\ConnectionInterface;

class PDOConnection extends Connection implements ConnectionInterface
{
    protected const REQUIRED_CONNECTION_KEYS= [
        'driver',
        'host',
        'port',
        'db_name',
        'username',
        'password',
        'default_fetch'
    ];

    public function connect(): void
    {
        $credentials = $this->parseCredentials($this->credentials);

        try {
            $this->connection = new PDO(...$credentials);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, pdo::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->credentials['default_fetch']);
        } catch (\Throwable $th) {
            throw new RuntimeException($th->getMessage());
        }
    }
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function parseCredentials(array $credentials): array
    {
        $dsn = \sprintf(
            '%s:host=%s;dbname=%s;port=%s',
            $credentials['driver'],
            $credentials['host'],
            $credentials['db_name'],
            $credentials['port'],
        );

        return [$dsn, $credentials['username'], $credentials['password']];
    }
}
