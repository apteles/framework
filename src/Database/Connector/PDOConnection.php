<?php
namespace ApTeles\Database\Connector;

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
        
        $dsn = $credentials[0] ?? null;
        $user = $credentials[1] ?? null;
        $pass = $credentials[2] ?? null;
        
        
        try {
            $this->connection = new PDO(
                $dsn,
                $user,
                $pass,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->credentials['default_fetch']| PDO::FETCH_CLASSTYPE);
        } catch (\Throwable $th) {
            throw new RuntimeException($th->getMessage());
        }
    }
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    protected function parseCredentials(array $credentials): array
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
