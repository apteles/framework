<?php
namespace ApTeles\Database\Contracts;

interface QueryBuilderInterface
{
    public function setConnection(ConnectionInterface $conn): void;

    public function table(string $table): QueryBuilderInterface;

    public function select(array $fields = ['*']): QueryBuilderInterface;

    public function create(array $data): QueryBuilderInterface;

    public function update(array $data): QueryBuilderInterface;

    public function delete(): QueryBuilderInterface;

    public function where(string $column, string $operator = self::OPERATORS[0], string $value = null): QueryBuilderInterface;

    public function runQuery(): QueryBuilderInterface;

    public function first(): object;

    public function get(): array;
}
