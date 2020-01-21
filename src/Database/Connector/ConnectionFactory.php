<?php
declare(strict_types=1);
namespace ApTeles\Database\Connector;

use ApTeles\Database\Contracts\ConnectionInterface;
use Exception;

class ConnectionFactory
{
    public static function make(string $type, array $options): ConnectionInterface
    {
        return self::createConnectionType($type, $options);
    }

    private static function createConnectionType(string $connectionType, array $options)
    {
        switch ($connectionType) {
            case 'mysql':
                $conn = new PDOConnection;
                $conn->setCredentials($options);
                $conn->connect();
                return $conn;
            case 'sqlite':
                $conn = new SqliteConnection;
                $conn->setCredentials($options);
                $conn->connect();
                return $conn;
            default:
                throw new Exception("Connection type: {$connectionType} not supported");

        }
    }
}
