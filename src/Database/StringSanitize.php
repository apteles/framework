<?php
namespace ApTeles\Database;

use ApTeles\Database\Contracts\SanitizeValueSQL;

class StringSanitize extends SanitizeValueSQL
{
    protected $value = '';

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function transform(): string
    {
        return "'{$this->value}'";
    }
}
