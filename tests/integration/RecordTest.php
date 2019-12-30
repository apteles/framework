<?php
declare(strict_types=1);

use ApTeles\Database\Record;
use PHPUnit\Framework\TestCase;
use ApTeles\Traits\Tests\DatabaseTests;

class RecordTest extends TestCase
{
    use DatabaseTests;

    public function setUp(): void
    {
        $this->up();
    }

    public function tearDown(): void
    {
        $this->down();
    }

    public function testItCanDeleteData()
    {
        $personEntity = new Person;
        $person = $personEntity->load(4);
        $result = $person->remove();
        $this->assertTrue((bool) $result);
    }

    public function testItCanLoadAllDataFromDatabase()
    {
        $personEntity = new Person;
        $result = $personEntity->all();
        $this->assertEquals((string) 4, $result[3]->id);
        $this->assertEquals('Oliver Dowd', $result[3]->name);
        $this->assertEquals('Oliver Dowd Street, 4', $result[3]->address);
        $this->assertEquals('(88) 1234-5678', $result[3]->phone);
        $this->assertEquals('naoenvie@email.com', $result[3]->email);
        $this->assertCount(4, $result);
    }

    public function testItShouldBeAbleUpdateData()
    {
        $data = [
            'name' => 'John Doe (updated)',
            'email'  => 'johndoe@acme.com.uk'
        ];

        $personEntity = new Person;
        $person = $personEntity->load(4);
        $result = $person->update($data);
        $result2 = $personEntity->load(4);

        $this->assertTrue((bool) $result);

        $this->assertEquals('John Doe (updated)', $result2->name);
        $this->assertEquals('johndoe@acme.com.uk', $result2->email);
    }

    public function testItShouldCreateData()
    {
        $data = [
            'name' => 'John Doe',
            'address' => 'Nowhere street, 99',
            'phone'   => '(99) 9999-9999',
            'email'  => 'johndoe@acme.com'
        ];

        $personEntity = new Person;
        $result = $personEntity->create($data);
        $result2 = $personEntity->load($result);

        $this->assertEquals((string) $result, $result2->id);
        $this->assertEquals('John Doe', $result2->name);
        $this->assertEquals('Nowhere street, 99', $result2->address);
        $this->assertEquals('(99) 9999-9999', $result2->phone);
        $this->assertEquals('johndoe@acme.com', $result2->email);
    }

    public function testItCanRetriveOneRegristry()
    {
        $personEntity = new Person;

        $result = $personEntity->load(1);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertEquals('1', $result->id);
        $this->assertEquals('Penelope Terry', $result->name);
        $this->assertEquals('Penelope Terry Street, 1', $result->address);
        $this->assertEquals('(88) 1234-5678', $result->phone);
        $this->assertEquals('naoenvie@email.com', $result->email);
    }

    protected function getOptions()
    {
        return  [
            'driver'    => 'sqlite',
            'file'          => __DIR__ . '/../database/fixture.sqlite',
            'username'    => '',
            'password'    => '',
            'default_fetch' => PDO::FETCH_CLASS

        ];

        // return [
        //     'driver'    => 'mysql',
        //     'host'    => '127.0.0.1',
        //     'port'    => '3306',
        //     'db_name'    => 'bug_report_testing',
        //     'username'    => 'root',
        //     'password'    => 'secret',
        //     'default_fetch' => PDO::FETCH_CLASS

        // ];
    }
    protected function getSQLDefinitionFixture()
    {
        return '
            DROP TABLE IF EXISTS person;
            CREATE TABLE IF NOT EXISTS person (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name text,
            address text,
            phone text,
            email text
        );';
    }

    protected function insertDataDefault()
    {
        return [
            "DELETE FROM person",
            "INSERT INTO person VALUES(1,'Penelope Terry','Penelope Terry Street, 1','(88) 1234-5678','naoenvie@email.com');",
            "INSERT INTO person VALUES(2,'James White','James White Street, 2','(88) 1234-5678','naoenvie@email.com');",
            "INSERT INTO person VALUES(3,'Anne Walsh','Anne Walsh Street, 3','(88) 1234-5678','naoenvie@email.com');",
            "INSERT INTO person VALUES(4,'Oliver Dowd','Oliver Dowd Street, 4','(88) 1234-5678','naoenvie@email.com');"
        ];
    }
}
class Person extends Record
{
    protected $table = 'person';
}
