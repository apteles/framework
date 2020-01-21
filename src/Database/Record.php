<?php
declare(strict_types=1);
namespace ApTeles\Database;

use Exception;
use ApTeles\Database\Contracts\ConnectionInterface;
use ApTeles\Database\Contracts\PersistenceInterface;
use ApTeles\Database\Contracts\QueryBuilderInterface;

abstract class Record implements PersistenceInterface
{
    /**
     *
     * @var string
     */
    protected $table;

    /**
     *
     * @var array
     */
    protected $data = [];

    /**
     * Temporary data for update operations
     * @var array
     */
    protected $dataTemp = [];

    /**
     *
     * @var ConnectionInterface
     */
    // protected $conn;

    // public function setConnection(ConnectionInterface $conn)
    // {
    //     $this->conn = $conn;
    //     return $this;
    // }

    abstract protected function connection(): ConnectionInterface;

    /**
     *
     * @return QueryBuilderInterface
     */
    public function getQueryBuilder(): QueryBuilderInterface
    {
        $builder = new QueyBuilder;
        $builder->setConnection($this->connection());

        return $builder;
    }

    /**
     *
     * @param array $data
     * @return integer
     */
    public function update(array $data): int
    {
        $this->dataTemp = $data;
        return $this->store();
    }

    /**
     *
     * @param array $data
     * @return integer
     */
    public function create(array $data): int
    {
        $this->hydrate($data);

        return $this->store();
    }

    /**
     *
     * @return integer
     */
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

    /**
     *
     * @return boolean
     */
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

    /**
     *
     * @param integer $id
     * @return void
     */
    public function load(int $id): object
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

    /**
     *
     * @param integer|null $id
     * @return integer
     */
    public function remove(?int $id = null): int
    {
        $id = $this->idIsFilled((string) $id);

        return $this->getQueryBuilder()->table($this->getEntity())
                        ->delete()
                        ->where('id', (string) $id)
                        ->runQuery()
                       ->count();
    }

    /**
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getQueryBuilder()
                    ->table($this->getEntity())
                        ->select()
                            ->runQuery()
                                ->fetchIntoCollection(\get_class($this)); //
    }

    /**
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     *
     * @param array $data
     * @return void
     */
    public function fill(array $data): void
    {
        $this->hydrate($data);
    }

    /**
     *
     * @param array $data
     * @return void
     */
    private function hydrate(array $data): void
    {
        if ($this->data) {
            $this->data = \array_merge($this->data, $data);
            return;
        }

        $this->data = $data;
        return;
    }

    /**
     *
     * @param string|null $property
     * @param string|null $value
     */
    public function __set(?string $property, $value)
    {
        /**
         * This method must be refactored, there ara a lot of logic
         * inside here.
         */
        $method = "define{$property}";
        if (\method_exists($this, $method)) {
            \call_user_func([$this, $method], $value);
        } else {
            $this->data[$property] = $value;
        }
    }

    /**
     *
     * @param string $property
     * @return void
     */
    public function __get(string $property)
    {
        /**
         * This method must be refactored, there ara a lot of logic
         * inside here.
         */
        $propertyMethod = ucfirst($property);
        $method = "retrive{$propertyMethod}";
        if (\method_exists($this, $method)) {
            return \call_user_func([$this, $method]);
        }

        return $this->getProperty($property);
    }

    /**
     *
     * @param string $perperty
     * @return void
     */
    private function resetProperty(string $perperty): void
    {
        unset($this->data[$perperty]);
    }

    /**
     *
     * @return string
     */
    public function getEntity(): string
    {
        $className = \get_called_class();
        if (!$this->table) {
            throw new Exception("Table name must be informed in class {$className}");
        }

        return $this->table;
    }

    /**
     *
     * @param string $property
     * @return string|null
     */
    private function getProperty(string $property)
    {
        return $this->data[$property] ?? null;
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        $this->resetProperty('id');
    }

    /**
     *
     * @param string $id
     * @return string
     */
    private function idIsFilled(string $id): string
    {
        /**
         * This method must be reviewed, its name is not
         * clear;
         *
         */
        if (empty($id)) {
            return $this->getProperty('id');
        }
        return $id;
    }

    /**
     *
     * @param [type] $property
     * @return boolean
     */
    public function __isset($property)
    {
        return isset($this->data[$property]);
    }
}
