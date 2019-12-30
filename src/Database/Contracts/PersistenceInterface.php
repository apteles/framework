<?php
declare(strict_types=1);

namespace ApTeles\Database\Contracts;

interface PersistenceInterface
{
    public function store();

    public function load(int $id);

    public function remove(int $id = null);
}
