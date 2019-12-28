<?php
declare(strict_types=1);
namespace ApTeles\Database;

class Orm
{
    private $test = [];
    public function __construct(string $test)
    {
        if ($test) {
            $this->test = $test;
        }
    }

    public function foo()
    {
        if ($this->test) {
            return $this->test;
        } else {
            return false;
        }
    }
}
