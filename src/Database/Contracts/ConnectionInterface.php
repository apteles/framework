<?php
declare(strict_types=1);
namespace ApTeles\Database\Contracts;

use PDO;

interface ConnectionInterface
{
    public function connect(): void;
    public function getConnection(): PDO;
}
