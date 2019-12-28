<?php
namespace ApTeles\Database;

use ApTeles\Database\Contracts\SanitizeValueSQL;

class NullSanitize extends SanitizeValueSQL
{
    private $value = '';

    public function __construct(?string $value)
    {
        $this->value = \strtoupper($value);
    }

    public function transform(): string
    {
        if (\is_null($this->value) || $this->value === 'NULL') {
            return "'{$this->value}'";
        }
    }
}
