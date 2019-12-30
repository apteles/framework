<?php
declare(strict_types=1);
namespace ApTeles\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use ApTeles\Database\QueyBuilder;
use ApTeles\Database\PDOConnection;

class BuilderTest extends TestCase
{
    /**
     *
     * @var QueyBuilder
     */
    protected $builder;

    public function setUp(): void
    {
        $this->builder = new QueyBuilder;
    }

    public function testItCanSetConnection()
    {
        $connMock = $this->getMockConnection();
        $connMock->expects($this->once())->method('getConnection')->willReturn(new PDOConnectionTestMockPDO);
        $this->builder->setConnection($connMock);
    }

    public function testItCanSelectWithWhereCondition()
    {
        $result = $this->builder->table('users')
                            ->select()
                                ->where('email', '=', 'admin@acme.com')
                                    ->dump();
        $this->assertEquals("SELECT `*` FROM `users` WHERE (email = ?)", $result);
    }

    public function testItCanSelectOmittingTheOperatorArgument()
    {
        $result = $this->builder->table('users')
                                ->select()
                                ->where('email', 'admin@acme.com')
                                ->dump();
        $this->assertEquals("SELECT `*` FROM `users` WHERE (email = ?)", $result);
    }

    public function testItCanSelectAllColumnsWithMultiplesWhereConditions()
    {
        $result = $this->builder->table('users')
                            ->select()
                                ->where('email', '=', 'admin@acme.com')
                                    ->where('admin', '=', (string) true)
                                    ->dump();
        $this->assertEquals("SELECT `*` FROM `users` WHERE (email = ? AND admin = ?)", $result);
    }

    public function testItCanSelectSpecificColumns()
    {
        $result = $this->builder->table('users')
        ->select(['id','name','email','created_at'])
       ->dump();

        $this->assertEquals("SELECT `id`, `name`, `email`, `created_at` FROM `users`", $result);
    }

    public function testItCanSelectAllColumnsInTable()
    {
        $result = $this->builder->table('users')
                                 ->select()
                                ->dump();
        $this->assertEquals("SELECT `*` FROM `users`", $result);
    }

    public function testItCanCreateData()
    {
        $result = $this->builder->table('users')->create([
            'name' => 'Administrator',
            'email' => 'admin@domain.com',
            'role'  => 'admin'
        ])->dump();
        $this->assertEquals("INSERT INTO users (`name`,`email`,`role`) VALUES (?,?,?)", $result);
    }

    public function testItCanUpdateData()
    {
        $result = $this->builder->table('users')
                            ->update(['name' => 'Adminitrator (updated)'])
                            ->dump();
        $this->assertEquals("UPDATE `users` SET name = 'Adminitrator (updated)'", $result);
    }

    public function testItCanDeleteData()
    {
        $result = $this->builder->table('users')->delete()->where('id', '=', (string) 10)->dump();

        $this->assertEquals("DELETE FROM `users` WHERE (id = ?)", $result);
    }

    public function testItShouldRunQueryAndReturnItSelfInstance()
    {
        $mockBuilder = $this->getMockBuilder(QueyBuilder::class)->setMethods(['runQuery'])->getMock();

        $mockBuilder->expects($this->once())->method('runQuery')->willReturn($this->builder);

        $mockBuilder->runQuery();
    }

    public function getMockConnection(array $methods = [], PDO $pdo = null)
    {
        $pdo = $pdo ?: new PDOConnectionTestMockPDO;
        $defaultsMethods = ['getConnection'];
        $connection = $this->getMockBuilder(PDOConnection::class)
                            ->setMethods(\array_merge($defaultsMethods, $methods))->getMock();

        return $connection;
    }
}

class PDOConnectionTestMockPDO extends PDO
{
    public function __construct()
    {
        # code...
    }
}
