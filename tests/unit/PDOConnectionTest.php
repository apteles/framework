<?php
declare(strict_types=1);
namespace ApTeles\Unit;

use PDO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ApTeles\Tests\Helpers\Helper;
use ApTeles\Database\PDOConnection;

class PDOConnectionTest extends TestCase
{
    /**
     *
     * @var PDOConnection $conn
     */
    private $conn = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->conn = new PDOConnection;

        $arr = [
            'driver'    => 'mysql',
            'host'    => '127.0.0.1',
            'port'    => '3306',
            'db_name'    => 'bug_report_testing',
            'username'    => 'root',
            'password'    => 'secret',
            'default_fetch' => PDO::FETCH_CLASS

        ];
    }

    public function testItgetCredentials()
    {
        $arr = [
            'driver'    => 'mysql',
            'host'    => '127.0.0.1',
            'port'    => '3306',
            'db_name'    => 'db_testing',
            'username'    => 'admin',
            'password'    => 'secret',
            'default_fetch' => PDO::FETCH_CLASS

        ];

        $this->assertArrayHasKey('driver', $arr);
        $this->assertArrayHasKey('host', $arr);
        $this->assertArrayHasKey('db_name', $arr);
        $this->assertArrayHasKey('username', $arr);
        $this->assertArrayHasKey('password', $arr);
        $this->assertArrayHasKey('default_fetch', $arr);

        return $arr;
    }

    /**
     * @depends testItgetCredentials
     */
    public function testItCanSetCredentials(array $credentials)
    {
        $result = $this->conn->setCredentials($credentials);

        $this->assertTrue($result);
    }
    /**
     * @depends testItgetCredentials
     */
    public function testItShouldReturnAnExceptionIfArgsIsMissing(array $args)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database connection credentials are not mapped correctly, required key: driver,host,port,db_name,username,password,default_fetch');

        unset($args['driver']);

        $this->conn->setCredentials($args);
    }

    /**
     * @depends testItgetCredentials
     */
    public function testItShouldReturnTrueIfHaveRequiredKeys(array $credentials)
    {
        $credentialsHaveRequiredKeys = Helper::turnMethodPublic(PDOConnection::class, 'credentialsHaveRequiredKeys');
        $result = $credentialsHaveRequiredKeys->invokeArgs($this->conn, [$credentials]);

        $this->assertTrue($result);
    }

    /**
     * @depends testItgetCredentials
     */
    public function testItShouldReturnFalseIfHaveRequiredKeys(array $credentials)
    {
        unset($credentials['driver']);

        $credentialsHaveRequiredKeys = Helper::turnMethodPublic(PDOConnection::class, 'credentialsHaveRequiredKeys');
        $result = $credentialsHaveRequiredKeys->invokeArgs($this->conn, [$credentials]);

        $this->assertFalse($result);
    }

    /**
     * @depends testItgetCredentials
     */
    public function testItCanFormatAnURIConnection(array $credentials)
    {
        $parseCredentials = Helper::turnMethodPublic(PDOConnection::class, 'parseCredentials');
        $result = $parseCredentials->invokeArgs($this->conn, [$credentials]);

        $this->assertEquals('mysql:host=127.0.0.1;dbname=db_testing;port=3306', $result[0]);
        $this->assertEquals('admin', $result[1]);
        $this->assertEquals('secret', $result[2]);
    }

    public function testItCanReturnAnInstanceOfPDO()
    {
        $this->markTestSkipped('test it later...');
    }

    public function testItCanConnectToDatabase()
    {
        $this->markTestSkipped('test it later...');
    }
}
