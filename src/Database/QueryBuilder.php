<?php
declare(strict_types=1);

namespace ApTeles\Database;

use PDO;
use Exception;
use PDOStatement;
use InvalidArgumentException;
use ApTeles\Database\Contracts\ConnectionInterface;

class QueyBuilder
{
    /**
     *
     * @var string
     */
    protected $table = '';
    /**
     *
     * @var PDO
     */
    protected $connection;

    /**
     *
     * @var PDOStatement
     */
    protected $statement;

    /**
     *
     * @var Criteria
     */
    protected $criteria;

    /**
     *
     * @var array
     */
    protected $fields = [];

    /**
     *
     * @var string
     */
    protected $query = '';

    /**
     * @var string
     */
    protected $placeholders = '';

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     *
     * @var string
     */
    protected $operation = self::DML_SELECT;

    protected const OPERATORS = ['=','>=','>','<=','<','<>', 'in', 'not in'];

    protected const PLACEHOLDER = "?";

    protected const DML_SELECT = 'SELECT';

    protected const DML_UPDATE = 'UPDATE';

    protected const DML_INSERT = 'INSERT';

    protected const DML_DELETE = 'DELETE';

    public function setConnection(ConnectionInterface $conn): void
    {
        $this->connection = $conn->getConnection();
    }

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function select(array $fields = ['*']): self
    {
        $this->operation = self::DML_SELECT;

        foreach ($fields as $field) {
            $this->fields[] = "`{$field}`";
        }

        return $this;
    }

    public function create(array $data): self
    {
        $this->fields = '`' . \implode('`,`', \array_keys($data)) . '`';
        $this->placeholders = $this->generatePlaceHolder($data)->transform();

        foreach ($data as $value) {
            $this->bindings[] = $value;
        }

        $this->operation = self::DML_INSERT;

        return $this;
    }

    public function update(array $data)
    {
        $this->operation = self::DML_UPDATE;

        foreach ($data as $column => $value) {
            $this->fields[] = (new Filter($column, self::OPERATORS[0], new StringSanitize($value)))->dump();
        }

        return $this;
    }

    public function delete(): self
    {
        $this->operation = self::DML_DELETE;
        return $this;
    }

    public function where(string $column, string $operator = self::OPERATORS[0], string $value= null)
    {
        if (!\in_array($operator, self::OPERATORS, true)) {
            if (!\is_null($value)) {
                throw new InvalidArgumentException("Operator is not valid");
            }
            $value = $operator;
            $operator = self::OPERATORS[0];
        }

        if (!$this->criteria) {
            $this->criteria = new Criteria;
        }

        $this->parseWhere([$column => $value], $operator);

        return $this;
    }

    public function whereIn(string $column, string $operator = self::OPERATORS[0], array $value= [])
    {
        if (!\in_array($operator, self::OPERATORS, true)) {
            if (!\is_null($value)) {
                throw new InvalidArgumentException("Operator is not valid");
            }
            $value = $operator;
            $operator = self::OPERATORS[0];
        }

        if (!$this->criteria) {
            $this->criteria = new Criteria;
        }

        $this->parseWhere([$column => $value], $operator);

        return $this;
    }

    private function parseWhere(array $conditions, string $operator): void
    {
        foreach ($conditions as $column => $value) {
            $placeholders = $this->generatePlaceHolder($value);

            $this->criteria->add(new Filter($column, $operator, $placeholders));
            $this->placeholders = $this->criteria->dump();
            $this->bindings[] =  $value;
        }
    }

    private function generatePlaceHolder(...$values)
    {
        foreach ($values as $value) {
            if (\is_string($value)) {
                return new PlaceHolderSQL(self::PLACEHOLDER);
            }
            if (\is_array($value)) {
                return new ArraySanitize(\array_fill(0, \count($value), self::PLACEHOLDER));
            }
        }
    }

    private function prepare(string $query): PDOStatement
    {
        return $this->connection->prepare($query);
    }

    private function sanitize(...$values): string
    {
        foreach ($values as $value) {
            if (\is_array($value)) {
                return (new ArraySanitize($value))->transform();
            }
            if (\is_bool($value)) {
                return (new BooleanSanitize($value))->transform();
            }
            if (\is_null($value)) {
                return (new NullSanitize($value))->transform();
            }
            if (\is_string($value)) {
                return (new StringSanitize($value))->transform();
            }
        }
    }

    public function getQuery(string $type)
    {
        switch ($type) {
            case self::DML_SELECT:
                return \sprintf("SELECT %s FROM `%s`", $this->getFields(), $this->getTable());
            case self::DML_DELETE:
                return \sprintf("DELETE FROM `%s`", $this->getTable());
            case self::DML_INSERT:
                return \sprintf("INSERT INTO %s (%s) VALUES %s", $this->getTable(), $this->fields, $this->placeholders);
            case self::DML_UPDATE:
                return \sprintf("UPDATE `%s` SET %s", $this->getTable(), $this->getFields());
        }
    }

    public function setProperty(string $property, string $value): void
    {
        if ($this->operation === self::DML_SELECT) {
            $this->criteria->setProperty($property, $value);
        }
    }

    public function dump()
    {
        $query = $this->getQuery($this->operation);
        if ($this->placeholders && $this->operation !== self::DML_INSERT) {
            $query .= " WHERE {$this->placeholders}";
        }
        return $query;
    }

    public function execute(object $statement)
    {
        $statement->execute($this->getBindings());

        $this->clearBindingsAndPlaceHolders();

        return $statement;
    }

    public function runQuery()
    {
        $query = $this->prepare($this->dump());


        $this->statement = $this->execute($query);
        return $this;
    }

    public function get()
    {
        return $this->statement->fetchAll();
    }

    public function count(): int
    {
        return $this->statement->rowCount();
    }

    public function lastInsertedId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    private function getBindings(): array
    {
        return \is_array($this->bindings[0]) ? $this->bindings[0] : $this->bindings;
    }

    public function clearBindingsAndPlaceHolders(): void
    {
        $this->bindings = [];
        $this->placesholders = [];
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }

    public function fetchInto(string $className = 'stdClass')
    {
        return $this->statement->fetchAll(PDO::FETCH_CLASS, $className);
    }

    public function getTable(): string
    {
        if (!$this->table) {
            throw new Exception("Table name was not defined. See method 'table'.");
        }

        return $this->table;
    }

    public function getFields(): string
    {
        return \implode(', ', $this->fields);
    }
}
