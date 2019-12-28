<?php
namespace ApTeles\Database;

use RuntimeException;
use ApTeles\Database\Contracts\Expression;

class Criteria extends Expression
{
    /**
     *
     * @var array<Expression>
     */
    private $expressions = [];

    /**
     *
     * @var array<string>
     */
    private $operators = [];

    private $properties = [];

    public function add(Expression $expression, $operator = self::AND_OPERATION): void
    {
        if (empty($this->expressions)) {
            $this->operators[] = '';
        }
        $this->expressions[] = $expression;
        $this->operators[] = $operator;
    }

    public function dump(): string
    {
        $result = '';

        if (!$this->expressions) {
            throw new RuntimeException("You must add expression before run this method");
        }
        foreach ($this->expressions as $key => $expresion) {
            $operator = $this->getOperator((int) $key);

            $result .= "{$operator} {$expresion->dump()} ";
        }

        return $this->clear($result);
    }

    public function setProperty(string $property, string $value): void
    {
        $this->properties[$property] = $value;
    }

    public function getProperty($property): string
    {
        return $this->properties[$property];
    }


    private function getOperator(int $key): string
    {
        return $this->operators[$key]?? '';
    }

    private function clear(string $value): string
    {
        $value = \trim($value);

        return "({$value})";
    }
}
