<?php
namespace ApTeles\Database;

use ApTeles\Database\Contracts\Expression;
use ApTeles\Database\Contracts\SanitizeValueSQL;

class Filter extends Expression
{
    /**
     *
     * @var string
     */
    private $column;

    /**
     *
     * @var string
     */
    private $operator;

    /**
     *
     * @var string
     */
    private $value;

    public function __construct(string $column, string $operator, SanitizeValueSQL $value)
    {
        $this->column = $column;

        $this->operator = $operator;

        $this->value =  $value->transform();
    }

    public function dump(): string
    {
        return "{$this->column} {$this->operator} {$this->value}";
    }
}
