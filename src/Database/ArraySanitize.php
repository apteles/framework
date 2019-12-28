<?php
namespace ApTeles\Database;

use ApTeles\Database\Contracts\SanitizeValueSQL;

class ArraySanitize extends SanitizeValueSQL
{
    private $value = [];

    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function transform(): string
    {
        $result = [];

        foreach ($this->value as $value) {
            if (\is_integer($value)) {
                $result[] = $value;
            }
            if (\is_string($value)) {
                $result[] = "{$value}";
            }
        }

        return '(' . \implode(',', $result) . ')';
    }
}
