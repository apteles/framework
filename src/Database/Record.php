<?php
declare(strict_types=1);
namespace ApTeles\Database;

use PDO;
use Exception;
use ApTeles\Database\Connector\ConnectionFactory;
use ApTeles\Database\Contracts\PersistenceInterface;
use ApTeles\Database\Contracts\QueryBuilderInterface;

abstract class Record implements PersistenceInterface
{
    /**
     * table name
     *
     * @var string
     */
    protected $table;

    protected $data = [];

    protected $dataTemp = [];

    public function getQueryBuilder(): QueryBuilderInterface
    {
        // $options = [
        //     'driver'    => 'mysql',
        //     'host'    => '127.0.0.1',
        //     'port'    => '3306',
        //     'db_name'    => 'bug_report_testing',
        //     'username'    => 'root',
        //     'password'    => 'secret',
        //     'default_fetch' => PDO::FETCH_CLASS

        // ];

        $options = [
            'driver'    => 'sqlite',
            'file'          => __DIR__ . '/../../tests/database/fixture.sqlite',
            'username'    => '',
            'password'    => '',
            'default_fetch' => PDO::FETCH_CLASS

        ];

        $builder = new QueyBuilder;
        $builder->setConnection(ConnectionFactory::make($options['driver'], $options));

        return $builder;
    }

    public function update(array $data): int
    {
        $this->dataTemp = $data;
        return $this->store();
    }

    public function create(array $data): int
    {
        $this->hydrate($data);

        return $this->store();
    }

    public function store(): int
    {
        if (!$this->idOrDataAlreadyExists()) {
            return $this->getQueryBuilder()
                    ->table($this->getEntity())
                     ->create($this->data)
                     ->runQuery()
                     ->lastInsertedId();
        }

        $id = $this->getProperty('id');
        $this->resetProperty('id');

        return $this->getQueryBuilder()
                    ->table($this->getEntity())
                     ->update($this->dataTemp)
                     ->where('id', $id)
                     ->runQuery()
                     ->count();
    }

    public function idOrDataAlreadyExists(): bool
    {
        if (
            \is_null($this->getProperty('id')) ||
            !$this->load((int) $this->id)
            ) {
            return false;
        }

        return true;
    }

    public function load(int $id)
    {
        $result = $this->getQueryBuilder()
                    ->table($this->getEntity())
                      ->select()
                       ->where('id', '=', (string) $id)
                        ->runQuery()
                          ->fetchOneObjectBy(\get_class($this));

        if ($result) {
            $this->fill($result->toArray());
            return $result;
        }
        throw new Exception("Data id: {$id} not found");
    }

    public function remove(?int $id = null)
    {
        $id = $this->idIsFilled((string) $id);

        return $this->getQueryBuilder()->table($this->getEntity())
                        ->delete()
                        ->where('id', (string) $id)
                        ->runQuery()
                       ->count();
    }

    public function all(): array
    {
        return $this->getQueryBuilder()
                    ->table($this->getEntity())
                        ->select()
                            ->runQuery()
                                ->fetchIntoCollection(\get_class($this));
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function fill(array $data): void
    {
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        if ($this->data) {
            $this->data = \array_merge($this->data, $data);
            return;
        }

        $this->data = $data;
        return;
    }

    public function __set(?string $property, ?string $value)
    {
        $method = "define{$property}";
        if (\method_exists($this, $method)) {
            \call_user_func([$this, $method], $value);
        } else {
            if (\is_null($value)) {
                $this->resetProperty($property);
            } else {
                $this->data[$property] = $value;
            }
        }
    }

    public function __get(string $property)
    {
        $method = "retive{$property}";
        if (\method_exists($this, $method)) {
            \call_user_func([$this, $method]);
        }

        return $this->getProperty($property);
    }

    private function resetProperty(string $perperty): void
    {
        unset($this->data['id']);
    }

    public function getEntity()
    {
        $className = \get_called_class();
        if (!$this->table) {
            throw new Exception("Table name must be informed in class {$className}");
        }

        return $this->table;
    }

    private function getProperty(string $property): ?string
    {
        return $this->data[$property] ?? null;
    }

    public function __clone()
    {
        $this->resetProperty('id');
    }

    private function idIsFilled(string $id): string
    {
        if (empty($id)) {
            return $this->getProperty('id');
        }
        return $id;
    }

    public function __isset($property)
    {
        return isset($this->data[$property]);
    }
}
