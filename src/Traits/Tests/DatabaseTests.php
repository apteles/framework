<?php
declare(strict_types=1);
namespace ApTeles\Traits\Tests;

use Exception;
use ApTeles\Database\Connector\ConnectionFactory;

trait DatabaseTests
{
    protected $conn;

    protected function boot()
    {
        $driver = $this->getOptions()['driver'];

        $this->conn = ConnectionFactory::make($driver, $this->getOptions())->getConnection();
        $this->bootCreateTable();
        $this->bootInsertData();
    }

    protected function up()
    {
        $this->boot();
        $this->conn->beginTransaction();
    }

    protected function down()
    {
        $this->conn->rollback();
    }
    protected function bootCreateTable(): void
    {
        if (!\method_exists($this, 'getSQLDefinitionFixture')) {
            throw new Exception("Method 'getSQLDefinitionFixture' must be implemented ");
        }
        $this->conn->exec($this->getSQLDefinitionFixture());
    }

    protected function bootInsertData(): void
    {
        if (!\method_exists($this, 'insertDataDefault')) {
            throw new Exception("Method 'insertDataDefault' must be implemented ");
        }
        foreach ($this->insertDataDefault() as $sql) {
            $this->conn->exec($sql);
        }
    }
}
